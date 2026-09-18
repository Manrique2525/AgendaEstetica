<?php

declare(strict_types=1);

namespace App\Actions;

use App\Exceptions\PublicBookingIdempotencyConflict;
use App\Exceptions\PublicBookingIdempotencyInProgress;
use App\Exceptions\PublicBookingRateLimitExceeded;
use App\Models\Appointment;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\Service;
use App\Support\CustomerPhoneNormalizer;
use App\Support\PublicBookingCustomerName;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use InvalidArgumentException;

final class CreatePublicBooking
{
    /**
     * @param  array{service_id: int, professional_id: int, starts_at: string, name: string, phone: string}  $data
     * @return array{response: array<string, mixed>, fingerprint: string, replayed: bool}
     */
    public function execute(array $data, string $idempotencyKey): array
    {
        $normalizer = new CustomerPhoneNormalizer;
        $normalizedPhone = $normalizer->normalize($data['phone']);
        $canonicalName = (new PublicBookingCustomerName)->canonicalize($data['name']);
        $startsAt = CarbonImmutable::parse($data['starts_at'])->utc();
        $fingerprint = hash('sha256', json_encode([
            'name' => $canonicalName,
            'phone' => $normalizedPhone,
            'professional_id' => $data['professional_id'],
            'service_id' => $data['service_id'],
            'starts_at' => $startsAt->toIso8601String(),
        ], JSON_THROW_ON_ERROR));
        $keyDigest = hash_hmac('sha256', $idempotencyKey, (string) config('app.key'));
        $cacheKey = "public-booking:idempotency:result:{$keyDigest}";
        $lockKey = "public-booking:idempotency:lock:{$keyDigest}";

        try {
            return Cache::lock($lockKey, 15)->block(2, function () use ($cacheKey, $data, $fingerprint, $normalizedPhone, $startsAt, $canonicalName): array {
                $cached = Cache::get($cacheKey);

                if (is_array($cached)) {
                    if (($cached['fingerprint'] ?? null) !== $fingerprint) {
                        throw new PublicBookingIdempotencyConflict;
                    }

                    return [
                        'response' => $cached['response'],
                        'fingerprint' => $fingerprint,
                        'replayed' => true,
                    ];
                }

                $phoneLimitKey = self::phoneLimitKey($normalizedPhone);
                if (! RateLimiter::attempt($phoneLimitKey, 3, static fn (): bool => true, 3600)) {
                    throw new PublicBookingRateLimitExceeded;
                }

                $appointment = DB::transaction(function () use ($data, $normalizedPhone, $startsAt, $canonicalName): Appointment {
                    $service = Service::query()
                        ->whereKey($data['service_id'])
                        ->where('active', true)
                        ->whereHas('category', fn ($query) => $query->where('active', true))
                        ->first();
                    $professional = Professional::query()
                        ->whereKey($data['professional_id'])
                        ->where('active', true)
                        ->whereHas('services', fn ($query) => $query
                            ->whereKey($data['service_id'])
                            ->where('active', true)
                            ->whereHas('category', fn ($category) => $category->where('active', true)))
                        ->first();

                    if ($service === null || $professional === null) {
                        throw new InvalidArgumentException('Appointment is unavailable.');
                    }

                    $profile = BusinessProfile::query()->where('singleton_key', 1)->firstOrFail();
                    if ($startsAt->lessThanOrEqualTo(CarbonImmutable::now($profile->timezone))) {
                        throw new InvalidArgumentException('Appointment is unavailable.');
                    }

                    $customers = Customer::query()
                        ->where('phone_normalized', $normalizedPhone)
                        ->limit(2)
                        ->get(['id', 'name', 'phone', 'phone_normalized']);
                    $customer = $customers->count() === 1
                        && (new PublicBookingCustomerName)->equivalent($customers[0]->name, $canonicalName)
                        ? $customers[0]
                        : Customer::query()->create([
                            'name' => $data['name'],
                            'phone' => $data['phone'],
                            'phone_normalized' => $normalizedPhone,
                        ]);

                    return (new CreateAppointment)->execute(
                        $customer,
                        $service,
                        $professional,
                        $startsAt,
                        $startsAt->addMinutes($service->duration_minutes),
                    );
                });

                $appointment->load(['service', 'professional']);
                /** @var Service $responseService */
                $responseService = $appointment->service;
                /** @var Professional $responseProfessional */
                $responseProfessional = $appointment->professional;
                /** @var CarbonInterface $responseStartsAt */
                $responseStartsAt = $appointment->starts_at;
                /** @var CarbonInterface $responseEndsAt */
                $responseEndsAt = $appointment->ends_at;
                $response = [
                    'message' => 'Tu cita fue confirmada.',
                    'status' => 'confirmed',
                    'service' => ['name' => $responseService->name],
                    'professional' => ['name' => $responseProfessional->name],
                    'starts_at' => $responseStartsAt->copy()->setTimezone('UTC')->toIso8601String(),
                    'ends_at' => $responseEndsAt->copy()->setTimezone('UTC')->toIso8601String(),
                ];

                Cache::put($cacheKey, [
                    'response' => $response,
                    'fingerprint' => $fingerprint,
                ], now()->addMinutes(15));

                return [
                    'response' => $response,
                    'fingerprint' => $fingerprint,
                    'replayed' => false,
                ];
            });
        } catch (LockTimeoutException) {
            throw new PublicBookingIdempotencyInProgress;
        }
    }

    public static function phoneLimitKey(string $normalizedPhone): string
    {
        return 'public-booking:phone:'.hash_hmac('sha256', $normalizedPhone, (string) config('app.key'));
    }
}
