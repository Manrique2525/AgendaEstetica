<?php

use App\Actions\CreateAppointment;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\TestResponse;

beforeEach(function (): void {
    Carbon::setTestNow(CarbonImmutable::parse('2026-01-05 08:00:00', 'UTC'));
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function publicBookingAppointmentFixture(): array
{
    $profile = BusinessProfile::factory()->create(['timezone' => 'UTC']);
    $category = ServiceCategory::factory()->create(['active' => true]);
    $service = Service::factory()->create([
        'service_category_id' => $category->id,
        'duration_minutes' => 45,
        'active' => true,
    ]);
    $professional = Professional::factory()->create(['active' => true]);
    $professional->services()->attach($service);
    $profile->hours()->create(['weekday' => 1, 'interval_order' => 1, 'opens_at' => '09:00', 'closes_at' => '12:00']);
    $professional->schedules()->create(['weekday' => 1, 'starts_at' => '09:00', 'ends_at' => '12:00']);

    return compact('profile', 'category', 'service', 'professional');
}

function publicBookingPayload(array $fixture, array $overrides = []): array
{
    return array_replace([
        'service_id' => $fixture['service']->id,
        'professional_id' => $fixture['professional']->id,
        'starts_at' => '2026-01-05T09:00:00Z',
        'name' => 'Ana Perez',
        'phone' => '993 229 4158',
    ], $overrides);
}

function postPublicBooking(array $payload, string $key): TestResponse
{
    return test()->withHeaders(['Idempotency-Key' => $key])->postJson('/api/v1/public/booking/appointments', $payload);
}

it('requires a valid Idempotency-Key and explicit timestamp', function (): void {
    $fixture = publicBookingAppointmentFixture();
    $payload = publicBookingPayload($fixture, ['starts_at' => '2026-01-05T09:00:00']);

    $this->postJson('/api/v1/public/booking/appointments', $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['starts_at', 'idempotency_key']);

    $this->withHeaders(['Idempotency-Key' => 'not-a-uuid'])
        ->postJson('/api/v1/public/booking/appointments', publicBookingPayload($fixture))
        ->assertStatus(422)
        ->assertJsonValidationErrors('idempotency_key');
});

it('creates a minimal Customer and confirmed Appointment through CreateAppointment', function (): void {
    $fixture = publicBookingAppointmentFixture();

    postPublicBooking(publicBookingPayload($fixture), '11111111-1111-4111-8111-111111111111')
        ->assertCreated()
        ->assertJsonPath('data.message', 'Tu cita fue confirmada.')
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.service.name', $fixture['service']->name)
        ->assertJsonPath('data.professional.name', $fixture['professional']->name)
        ->assertJsonPath('data.starts_at', '2026-01-05T09:00:00+00:00')
        ->assertJsonPath('data.ends_at', '2026-01-05T09:45:00+00:00')
        ->assertJsonMissingPath('data.customer_id')
        ->assertJsonMissingPath('data.phone_normalized')
        ->assertJsonMissingPath('data.history');

    $appointment = Appointment::query()->firstOrFail();
    expect(Customer::query()->count())->toBe(1)
        ->and($appointment->status)->toBe(AppointmentStatus::CONFIRMED)
        ->and($appointment->duration_minutes)->toBe(45)
        ->and($appointment->history()->where('event_type', 'created')->count())->toBe(1);
});

it('reuses exactly one Customer only when the normalized name is equivalent', function (): void {
    $fixture = publicBookingAppointmentFixture();
    $customer = Customer::factory()->create(['name' => 'Ana Perez', 'phone' => '+529932294158', 'phone_normalized' => '+529932294158']);

    postPublicBooking(publicBookingPayload($fixture, ['name' => '  ana   perez  ', 'phone' => '993-229-4158']), '22222222-2222-4222-8222-222222222222')
        ->assertCreated();

    expect(Customer::query()->count())->toBe(1)
        ->and(Appointment::query()->firstOrFail()->customer_id)->toBe($customer->id);
});

it('creates a new Customer for a different name without mutating the existing Customer', function (): void {
    $fixture = publicBookingAppointmentFixture();
    $existing = Customer::factory()->create(['name' => 'Ana Perez', 'phone' => '+529932294158', 'phone_normalized' => '+529932294158']);

    postPublicBooking(publicBookingPayload($fixture, ['name' => 'Juan Perez']), '33333333-3333-4333-8333-333333333333')
        ->assertCreated();

    expect(Customer::query()->count())->toBe(2)
        ->and($existing->refresh()->name)->toBe('Ana Perez')
        ->and($existing->phone)->toBe('+529932294158')
        ->and(Appointment::query()->firstOrFail()->customer_id)->not->toBe($existing->id);
});

it('creates a new Customer when multiple normalized-phone matches exist', function (): void {
    $fixture = publicBookingAppointmentFixture();
    $first = Customer::factory()->create(['phone_normalized' => '+529932294158']);
    $second = Customer::factory()->create(['phone_normalized' => '+529932294158']);

    postPublicBooking(publicBookingPayload($fixture), '44444444-4444-4444-8444-444444444444')
        ->assertCreated();

    expect(Customer::query()->count())->toBe(3)
        ->and(Appointment::query()->firstOrFail()->customer_id)->not->toBeIn([$first->id, $second->id]);
});

it('rolls back a newly created Customer when the Appointment is unavailable', function (): void {
    $fixture = publicBookingAppointmentFixture();
    $existing = Customer::factory()->create();
    (new CreateAppointment)->execute(
        $existing,
        $fixture['service'],
        $fixture['professional'],
        CarbonImmutable::parse('2026-01-05 09:00:00', 'UTC'),
        CarbonImmutable::parse('2026-01-05 09:45:00', 'UTC'),
    );

    postPublicBooking(publicBookingPayload($fixture, ['name' => 'Nuevo Cliente']), '55555555-5555-4555-8555-555555555555')
        ->assertStatus(409)
        ->assertJsonPath('code', 'appointment_unavailable');

    $digest = hash_hmac('sha256', '55555555-5555-4555-8555-555555555555', (string) config('app.key'));
    expect(Customer::query()->count())->toBe(1)
        ->and(Customer::query()->firstOrFail()->id)->toBe($existing->id)
        ->and(Appointment::query()->count())->toBe(1)
        ->and(Cache::has("public-booking:idempotency:result:{$digest}"))->toBeFalse();
});

it('leaves a reused Customer unchanged when the Appointment fails', function (): void {
    $fixture = publicBookingAppointmentFixture();
    $existing = Customer::factory()->create(['name' => 'Ana Perez', 'phone' => '+529932294158', 'phone_normalized' => '+529932294158']);
    (new CreateAppointment)->execute(
        $existing,
        $fixture['service'],
        $fixture['professional'],
        CarbonImmutable::parse('2026-01-05 09:00:00', 'UTC'),
        CarbonImmutable::parse('2026-01-05 09:45:00', 'UTC'),
    );

    postPublicBooking(publicBookingPayload($fixture), '66666666-6666-4666-8666-666666666666')
        ->assertStatus(409)
        ->assertJsonPath('code', 'appointment_unavailable');

    expect(Customer::query()->count())->toBe(1)
        ->and($existing->refresh()->name)->toBe('Ana Perez')
        ->and($existing->phone_normalized)->toBe('+529932294158');
});

it('uses fresh Service duration and ignores unauthorized booking fields', function (): void {
    $fixture = publicBookingAppointmentFixture();
    $fixture['service']->update(['duration_minutes' => 60]);

    postPublicBooking(publicBookingPayload($fixture, [
        'ends_at' => '2026-01-05T09:01:00Z',
        'duration_minutes' => 1,
        'status' => 'cancelled',
        'customer_id' => 999999,
        'price' => '0.00',
        'source' => 'public',
        'notes' => 'internal',
    ]), '77777777-7777-4777-8777-777777777777')
        ->assertCreated()
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.ends_at', '2026-01-05T10:00:00+00:00');

    expect(Appointment::query()->firstOrFail()->duration_minutes)->toBe(60)
        ->and(Appointment::query()->firstOrFail()->ends_at->toIso8601String())->toBe('2026-01-05T10:00:00+00:00');
});

it('rejects stale inactive or incompatible resources without creating a Customer', function (): void {
    $fixture = publicBookingAppointmentFixture();
    $fixture['service']->update(['active' => false]);

    postPublicBooking(publicBookingPayload($fixture), '88888888-8888-4888-8888-888888888888')
        ->assertStatus(409)
        ->assertJsonPath('code', 'appointment_unavailable');

    expect(Customer::query()->count())->toBe(0)->and(Appointment::query()->count())->toBe(0);
});

it('replays a successful booking for the same key and fingerprint without a second Appointment', function (): void {
    $fixture = publicBookingAppointmentFixture();
    $payload = publicBookingPayload($fixture);
    $key = '99999999-9999-4999-8999-999999999999';

    $first = postPublicBooking($payload, $key)->assertCreated();
    $second = postPublicBooking($payload, $key)
        ->assertCreated()
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.starts_at', '2026-01-05T09:00:00+00:00');

    expect($second->json('data'))->toEqual($first->json('data'))
        ->and(Appointment::query()->count())->toBe(1)
        ->and(Customer::query()->count())->toBe(1)
        ->and(Appointment::query()->firstOrFail()->history()->count())->toBe(1);
});

it('expires successful idempotency results after fifteen minutes', function (): void {
    $fixture = publicBookingAppointmentFixture();
    $key = 'ffffffff-ffff-4fff-8fff-ffffffffffff';
    postPublicBooking(publicBookingPayload($fixture), $key)->assertCreated();
    $digest = hash_hmac('sha256', $key, (string) config('app.key'));

    expect(Cache::has("public-booking:idempotency:result:{$digest}"))->toBeTrue();
    Carbon::setTestNow(CarbonImmutable::parse('2026-01-05 08:16:00', 'UTC'));
    expect(Cache::has("public-booking:idempotency:result:{$digest}"))->toBeFalse();
});

it('rejects the same key with a different fingerprint without creating more data', function (): void {
    $fixture = publicBookingAppointmentFixture();
    $key = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
    postPublicBooking(publicBookingPayload($fixture), $key)->assertCreated();

    postPublicBooking(publicBookingPayload($fixture, ['starts_at' => '2026-01-05T10:00:00Z']), $key)
        ->assertStatus(409)
        ->assertJsonPath('code', 'idempotency_key_conflict');

    expect(Appointment::query()->count())->toBe(1)->and(Customer::query()->count())->toBe(1);
});

it('allows different keys to reach SPEC-004 conflict handling for the same slot', function (): void {
    $fixture = publicBookingAppointmentFixture();
    postPublicBooking(publicBookingPayload($fixture), 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb')->assertCreated();

    postPublicBooking(publicBookingPayload($fixture, ['name' => 'Otra Persona']), 'cccccccc-cccc-4ccc-8ccc-cccccccccccc')
        ->assertStatus(409)
        ->assertJsonPath('code', 'appointment_unavailable');

    expect(Appointment::query()->count())->toBe(1)->and(Customer::query()->count())->toBe(1);
});

it('does not cache an unavailable failure as a successful idempotent response', function (): void {
    $fixture = publicBookingAppointmentFixture();
    $existing = Customer::factory()->create();
    $blocking = Appointment::factory()->create([
        'customer_id' => $existing->id,
        'service_id' => $fixture['service']->id,
        'professional_id' => $fixture['professional']->id,
        'starts_at' => '2026-01-05 09:00:00',
        'ends_at' => '2026-01-05 09:45:00',
        'duration_minutes' => 45,
        'status' => AppointmentStatus::CONFIRMED,
    ]);
    $key = 'dddddddd-dddd-4ddd-8ddd-dddddddddddd';

    postPublicBooking(publicBookingPayload($fixture, ['name' => 'Retry Client']), $key)
        ->assertStatus(409)->assertJsonPath('code', 'appointment_unavailable');
    $blocking->delete();

    postPublicBooking(publicBookingPayload($fixture, ['name' => 'Retry Client']), $key)
        ->assertCreated();
});

it('supports atomic locks on the configured cache store', function (): void {
    $lock = Cache::lock('public-booking-test-lock', 5);

    expect($lock->get())->toBeTrue();
    $lock->release();
});

it('returns a bounded in-progress conflict when the same idempotency lock is held', function (): void {
    $fixture = publicBookingAppointmentFixture();
    $key = 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee';
    $digest = hash_hmac('sha256', $key, (string) config('app.key'));
    $lock = Cache::lock("public-booking:idempotency:lock:{$digest}", 15);

    Carbon::setTestNow();

    expect($lock->get())->toBeTrue();

    postPublicBooking(publicBookingPayload($fixture), $key)
        ->assertStatus(409)
        ->assertJsonPath('code', 'idempotency_request_in_progress');

    $lock->release();
});

it('makes a public confirmed booking visible through the existing Admin Agenda read', function (): void {
    $fixture = publicBookingAppointmentFixture();
    $admin = User::factory()->create();

    postPublicBooking(publicBookingPayload($fixture), '12121212-1212-4121-8121-121212121212')
        ->assertCreated();

    $this->actingAs($admin)
        ->getJson('/api/v1/admin/agenda/appointments?from=2026-01-05&to=2026-01-06')
        ->assertOk()
        ->assertJsonPath('data.0.status', 'confirmed')
        ->assertJsonPath('data.0.service.id', $fixture['service']->id)
        ->assertJsonPath('data.0.professional.id', $fixture['professional']->id);
});
