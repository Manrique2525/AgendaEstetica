<?php

use App\Actions\CreateAppointment;
use App\Actions\CreatePublicBooking;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\Service;
use App\Models\ServiceCategory;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Testing\TestResponse;

function publicBookingSecurityFixture(): array
{
    $profile = BusinessProfile::factory()->create(['timezone' => 'UTC']);
    $category = ServiceCategory::factory()->create(['active' => true]);
    $service = Service::factory()->create(['service_category_id' => $category->id, 'duration_minutes' => 45, 'active' => true]);
    $professional = Professional::factory()->create(['active' => true]);
    $professional->services()->attach($service);
    $profile->hours()->create(['weekday' => 1, 'interval_order' => 1, 'opens_at' => '09:00', 'closes_at' => '12:00']);
    $professional->schedules()->create(['weekday' => 1, 'starts_at' => '09:00', 'ends_at' => '12:00']);

    return compact('profile', 'category', 'service', 'professional');
}

function publicBookingSecurityPayload(array $fixture, array $overrides = []): array
{
    return array_replace([
        'service_id' => $fixture['service']->id,
        'professional_id' => $fixture['professional']->id,
        'starts_at' => '2026-01-05T09:00:00Z',
        'name' => 'Ana Perez',
        'phone' => '993 229 4158',
    ], $overrides);
}

function publicBookingSecurityPost(array $payload, string $key, string $ip = '198.51.100.40'): TestResponse
{
    return test()->withHeaders(['Idempotency-Key' => $key])
        ->withServerVariables(['REMOTE_ADDR' => $ip])
        ->postJson('/api/v1/public/booking/appointments', $payload);
}

beforeEach(function (): void {
    config(['cache.default' => 'database']);
    Carbon::setTestNow(Carbon::parse('2026-01-05 08:00:00', 'UTC'));
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('allows five booking requests per IP and returns a safe JSON 429 on the sixth', function (): void {
    $fixture = publicBookingSecurityFixture();

    foreach (range(1, 5) as $attempt) {
        publicBookingSecurityPost(publicBookingSecurityPayload($fixture, [
            'phone' => '99322941'.(50 + $attempt),
        ]), sprintf('10000000-0000-4000-8000-%012d', $attempt), '198.51.100.50')
            ->assertStatus($attempt === 1 ? 201 : 409);
    }

    publicBookingSecurityPost(publicBookingSecurityPayload($fixture, ['phone' => '9932294160']), '10000000-0000-4000-8000-000000000006', '198.51.100.50')
        ->assertTooManyRequests()
        ->assertExactJson(['message' => 'No pudimos procesar tantas solicitudes. Intenta más tarde.', 'code' => 'too_many_requests'])
        ->assertHeader('Retry-After')
        ->assertJsonMissingPath('ip')
        ->assertJsonMissingPath('exception');
});

it('keeps booking IP budgets independent', function (): void {
    $fixture = publicBookingSecurityFixture();

    publicBookingSecurityPost(publicBookingSecurityPayload($fixture, ['phone' => '9932294158']), '11000000-0000-4000-8000-000000000001', '198.51.100.51')->assertCreated();
    publicBookingSecurityPost(publicBookingSecurityPayload($fixture, ['phone' => '9932294159']), '11000000-0000-4000-8000-000000000002', '198.51.100.52')->assertStatus(409);
});

it('limits three valid new executions per normalized phone and rejects the fourth', function (): void {
    $fixture = publicBookingSecurityFixture();
    $phones = ['993 229 4158', '993-229-4158', '+52 993 229 4158', '9932294158'];

    foreach (array_slice($phones, 0, 3) as $index => $phone) {
        publicBookingSecurityPost(publicBookingSecurityPayload($fixture, ['phone' => $phone, 'name' => 'Cliente '.$index]), sprintf('12000000-0000-4000-8000-%012d', $index + 1), '198.51.100.'.(60 + $index))
            ->assertStatus($index === 0 ? 201 : 409);
    }

    publicBookingSecurityPost(publicBookingSecurityPayload($fixture, ['phone' => $phones[3], 'name' => 'Cuarta']), '12000000-0000-4000-8000-000000000004', '198.51.100.63')
        ->assertTooManyRequests()
        ->assertExactJson(['message' => 'No pudimos procesar tantas solicitudes. Intenta más tarde.', 'code' => 'too_many_requests']);
});

it('keeps distinct normalized phones in independent limiter buckets', function (): void {
    $fixture = publicBookingSecurityFixture();

    foreach (range(1, 3) as $attempt) {
        publicBookingSecurityPost(publicBookingSecurityPayload($fixture, [
            'phone' => '9932294158',
            'name' => 'Cliente '.$attempt,
        ]), sprintf('12500000-0000-4000-8000-%012d', $attempt), '198.51.100.'.(70 + $attempt))
            ->assertStatus($attempt === 1 ? 201 : 409);
    }

    publicBookingSecurityPost(publicBookingSecurityPayload($fixture, ['phone' => '9932294159']), '12500000-0000-4000-8000-000000000004', '198.51.100.74')
        ->assertStatus(409)
        ->assertJsonPath('code', 'appointment_unavailable');
});

it('uses deterministic HMAC phone keys without raw phone material', function (): void {
    $fixture = publicBookingSecurityFixture();
    $normalized = '+529932294158';
    $key = CreatePublicBooking::phoneLimitKey($normalized);
    RateLimiter::clear($key);

    publicBookingSecurityPost(publicBookingSecurityPayload($fixture), '13000000-0000-4000-8000-000000000001', '198.51.100.70')->assertCreated();

    expect($key)->toBe(CreatePublicBooking::phoneLimitKey($normalized))
        ->not->toContain('9932294158')
        ->not->toContain($normalized)
        ->and(RateLimiter::remaining($key, 3))->toBe(2);
});

it('does not consume phone quota for a successful idempotent replay', function (): void {
    $fixture = publicBookingSecurityFixture();
    $key = '14000000-0000-4000-8000-000000000001';
    $payload = publicBookingSecurityPayload($fixture);

    publicBookingSecurityPost($payload, $key, '198.51.100.80')->assertCreated();
    publicBookingSecurityPost($payload, $key, '198.51.100.80')->assertCreated();

    expect(RateLimiter::remaining(CreatePublicBooking::phoneLimitKey('+529932294158'), 3))->toBe(2);
});

it('does not consume phone quota for an idempotency conflict', function (): void {
    $fixture = publicBookingSecurityFixture();
    $key = '15000000-0000-4000-8000-000000000001';
    $payload = publicBookingSecurityPayload($fixture);

    publicBookingSecurityPost($payload, $key, '198.51.100.90')->assertCreated();
    publicBookingSecurityPost($payload, $key, '198.51.100.90')->assertCreated();
    expect(RateLimiter::remaining(CreatePublicBooking::phoneLimitKey('+529932294158'), 3))->toBe(2);
});

it('counts a valid unavailable execution but not a malformed transport request', function (): void {
    $fixture = publicBookingSecurityFixture();
    $existing = Customer::factory()->create();
    (new CreateAppointment)->execute($existing, $fixture['service'], $fixture['professional'], CarbonImmutable::parse('2026-01-05 09:00:00', 'UTC'), CarbonImmutable::parse('2026-01-05 09:45:00', 'UTC'));

    publicBookingSecurityPost(publicBookingSecurityPayload($fixture), '17000000-0000-4000-8000-000000000001', '198.51.100.100')->assertStatus(409)->assertJsonPath('code', 'appointment_unavailable');
    expect(RateLimiter::remaining(CreatePublicBooking::phoneLimitKey('+529932294158'), 3))->toBe(2);

    publicBookingSecurityPost(publicBookingSecurityPayload($fixture, ['starts_at' => 'not-an-instant']), '18000000-0000-4000-8000-000000000001', '198.51.100.101')->assertStatus(422);
    expect(RateLimiter::remaining(CreatePublicBooking::phoneLimitKey('+529932294158'), 3))->toBe(2);
});

it('returns a generic safe JSON 500 for unexpected public booking failures', function (): void {
    $request = Request::create('/api/v1/public/booking/appointments', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $response = app(ExceptionHandler::class)->render($request, new RuntimeException('SQLSTATE secret table details'));

    expect($response->getStatusCode())->toBe(500)
        ->and($response->getData(true))->toBe(['message' => 'Server error.'])
        ->and($response->headers->get('content-type'))->toContain('application/json');
});
