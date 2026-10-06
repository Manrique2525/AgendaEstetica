<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\BusinessProfile;
use App\Models\Professional;
use App\Models\Service;
use App\Support\PublicBookingLocalTime;
use Carbon\CarbonImmutable;
use DateTimeZone;
use InvalidArgumentException;

final class ListPublicBookableTimes
{
    /**
     * @return array{date: string, timezone: string, slots: array<int, array{starts_at: string, ends_at: string, local_start: string, local_end: string}>}|null
     */
    public function execute(int $serviceId, int $professionalId, string $date): ?array
    {
        $profile = BusinessProfile::query()->where('singleton_key', 1)->firstOrFail();
        $timezone = new DateTimeZone($profile->timezone);
        $today = CarbonImmutable::now($timezone)->startOfDay();
        $requestedDate = CarbonImmutable::createFromFormat('!Y-m-d', $date, $timezone);

        if ($requestedDate === null || $requestedDate->format('Y-m-d') !== $date) {
            throw new InvalidArgumentException('The booking date is invalid.');
        }

        if ($requestedDate->lt($today) || $requestedDate->gt($today->addDays(90))) {
            throw new InvalidArgumentException('The booking date is outside the allowed horizon.');
        }

        $service = Service::query()
            ->whereKey($serviceId)
            ->where('active', true)
            ->whereHas('category', fn ($query) => $query->where('active', true))
            ->first();
        $professional = Professional::query()
            ->whereKey($professionalId)
            ->where('active', true)
            ->whereHas('services', fn ($query) => $query
                ->whereKey($serviceId)
                ->where('active', true)
                ->whereHas('category', fn ($category) => $category->where('active', true)))
            ->first();

        if ($service === null || $professional === null) {
            return null;
        }

        $now = CarbonImmutable::now($timezone);
        $slots = [];
        $resolver = new PublicBookingLocalTime;

        for ($minutes = 0; $minutes < 24 * 60; $minutes += 15) {
            $hour = intdiv($minutes, 60);
            $minute = $minutes % 60;
            $localStart = sprintf('%02d:%02d', $hour, $minute);
            $startsAt = $resolver->resolve($date, $localStart, $timezone);

            if ($startsAt === null || ($date === $today->format('Y-m-d') && $startsAt->lessThanOrEqualTo($now))) {
                continue;
            }

            $endsAt = $startsAt->addMinutes($service->duration_minutes);

            if (! (new CheckAppointmentAvailability)->execute($service, $professional, $startsAt->utc(), $endsAt->utc())) {
                continue;
            }

            $slots[] = [
                'starts_at' => $startsAt->utc()->toIso8601String(),
                'ends_at' => $endsAt->utc()->toIso8601String(),
                'local_start' => $startsAt->format('H:i'),
                'local_end' => $endsAt->format('H:i'),
            ];
        }

        return [
            'date' => $date,
            'timezone' => $profile->timezone,
            'slots' => $slots,
        ];
    }
}
