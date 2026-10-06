<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\ProfessionalTimeOff;
use App\Models\Service;
use App\Models\ServiceCategory;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    Carbon::setTestNow(CarbonImmutable::parse('2026-01-05 08:00:00', 'UTC'));
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function publicAvailabilityFixture(string $timezone = 'UTC', bool $withSchedule = true): array
{
    $profile = BusinessProfile::factory()->create(['timezone' => $timezone]);
    $category = ServiceCategory::factory()->create(['active' => true]);
    $service = Service::factory()->create([
        'service_category_id' => $category->id,
        'duration_minutes' => 45,
        'active' => true,
    ]);
    $professional = Professional::factory()->create(['active' => true]);
    $professional->services()->attach($service);

    if ($withSchedule) {
        $profile->hours()->create(['weekday' => 1, 'interval_order' => 1, 'opens_at' => '09:00', 'closes_at' => '12:00']);
        $professional->schedules()->create(['weekday' => 1, 'starts_at' => '09:00', 'ends_at' => '12:00']);
    }

    return compact('profile', 'category', 'service', 'professional');
}

it('returns canonical future slots with exact Service duration and no Customer queries', function (): void {
    $fixture = publicAvailabilityFixture();
    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = strtolower($query->sql);
    });

    $response = $this->getJson("/api/v1/public/booking/availability?service_id={$fixture['service']->id}&professional_id={$fixture['professional']->id}&date=2026-01-05")
        ->assertOk()
        ->assertJsonPath('data.date', '2026-01-05')
        ->assertJsonPath('data.timezone', 'UTC');
    $slots = $response->json('data.slots');

    expect($slots)->toHaveCount(10)
        ->and(collect($slots)->pluck('local_start')->all())->toBe(['09:00', '09:15', '09:30', '09:45', '10:00', '10:15', '10:30', '10:45', '11:00', '11:15'])
        ->and(collect($slots)->pluck('local_start')->unique())->toHaveCount(count($slots))
        ->and(collect($slots)->every(fn (array $slot): bool => (int) substr($slot['local_start'], -2) % 15 === 0))->toBeTrue()
        ->and((int) abs(CarbonImmutable::parse($slots[0]['ends_at'])->diffInMinutes(CarbonImmutable::parse($slots[0]['starts_at']))))->toBe(45)
        ->and($slots[0]['starts_at'])->toMatch('/\+00:00$/')
        ->and(collect($queries)->filter(fn (string $sql): bool => str_contains($sql, 'customers'))->all())->toBe([]);
});

it('uses the current Service duration when deriving candidate ends', function (): void {
    $fixture = publicAvailabilityFixture();
    $fixture['service']->update(['duration_minutes' => 60]);

    $slot = $this->getJson("/api/v1/public/booking/availability?service_id={$fixture['service']->id}&professional_id={$fixture['professional']->id}&date=2026-01-05")
        ->assertOk()->json('data.slots.0');

    expect((int) abs(CarbonImmutable::parse($slot['ends_at'])->diffInMinutes(CarbonImmutable::parse($slot['starts_at']))))->toBe(60);
});

it('rejects malformed, past and beyond-horizon availability dates', function (string $query): void {
    $fixture = publicAvailabilityFixture();
    Carbon::setTestNow(CarbonImmutable::parse('2026-01-05 08:00:00', 'UTC'));

    $this->getJson("/api/v1/public/booking/availability?service_id={$fixture['service']->id}&professional_id={$fixture['professional']->id}&date={$query}")
        ->assertStatus(422)
        ->assertJsonValidationErrors('date');

    Carbon::setTestNow();
})->with(['invalid' => '2026-02-30', 'past' => '2026-01-04', 'beyond' => '2026-04-06']);

it('allows the inclusive ninety-day horizon boundary', function (): void {
    $fixture = publicAvailabilityFixture();
    Carbon::setTestNow(CarbonImmutable::parse('2026-01-05 08:00:00', 'UTC'));

    $this->getJson("/api/v1/public/booking/availability?service_id={$fixture['service']->id}&professional_id={$fixture['professional']->id}&date=2026-04-05")
        ->assertOk()
        ->assertJsonPath('data.date', '2026-04-05');

    Carbon::setTestNow();
});

it('excludes elapsed starts today without adding a minimum lead time', function (): void {
    $fixture = publicAvailabilityFixture();
    Carbon::setTestNow(CarbonImmutable::parse('2026-01-05 10:07:00', 'UTC'));

    $slots = $this->getJson("/api/v1/public/booking/availability?service_id={$fixture['service']->id}&professional_id={$fixture['professional']->id}&date=2026-01-05")
        ->assertOk()->json('data.slots');

    expect(collect($slots)->pluck('local_start')->all())
        ->not->toContain('10:00')
        ->toContain('10:15');

    Carbon::setTestNow();
});

it('omits nonexistent spring-forward and ambiguous fall-back starts', function (): void {
    $spring = publicAvailabilityFixture('America/New_York', false);
    $spring['profile']->hours()->create(['weekday' => 7, 'interval_order' => 1, 'opens_at' => '00:00', 'closes_at' => '04:00']);
    $spring['professional']->schedules()->create(['weekday' => 7, 'starts_at' => '00:00', 'ends_at' => '04:00']);
    $springSlots = $this->getJson("/api/v1/public/booking/availability?service_id={$spring['service']->id}&professional_id={$spring['professional']->id}&date=2026-03-08")
        ->assertOk()->json('data.slots');

    Carbon::setTestNow(CarbonImmutable::parse('2026-09-01 08:00:00', 'UTC'));
    $fallSlots = $this->getJson("/api/v1/public/booking/availability?service_id={$spring['service']->id}&professional_id={$spring['professional']->id}&date=2026-11-01")
        ->assertOk()->json('data.slots');

    expect(collect($springSlots)->pluck('local_start')->filter(fn (string $time): bool => str_starts_with($time, '02:')))->toHaveCount(0)
        ->and(collect($fallSlots)->pluck('local_start')->filter(fn (string $time): bool => str_starts_with($time, '01:')))->toHaveCount(0);
});

it('returns empty slots for a valid date with no schedule', function (): void {
    $fixture = publicAvailabilityFixture('UTC', false);

    $this->getJson("/api/v1/public/booking/availability?service_id={$fixture['service']->id}&professional_id={$fixture['professional']->id}&date=2026-01-05")
        ->assertOk()->assertExactJson(['data' => [
            'date' => '2026-01-05',
            'timezone' => 'UTC',
            'slots' => [],
        ]]);
});

it('maps unusable resources to generic appointment_unavailable', function (): void {
    $fixture = publicAvailabilityFixture();
    $fixture['service']->update(['active' => false]);

    $this->getJson("/api/v1/public/booking/availability?service_id={$fixture['service']->id}&professional_id={$fixture['professional']->id}&date=2026-01-05")
        ->assertStatus(409)
        ->assertJsonPath('code', 'appointment_unavailable')
        ->assertJsonMissing(['service_id' => $fixture['service']->id])
        ->assertJsonMissing(['active' => false]);
});

it('maps inactive or incompatible Professionals to generic appointment_unavailable', function (): void {
    $fixture = publicAvailabilityFixture();
    $inactive = Professional::factory()->create(['active' => false]);
    $inactive->services()->attach($fixture['service']);
    $otherService = Service::factory()->create([
        'service_category_id' => $fixture['category']->id,
        'active' => true,
    ]);
    $incompatible = Professional::factory()->create(['active' => true]);
    $incompatible->services()->attach($otherService);

    foreach ([$inactive, $incompatible] as $professional) {
        $this->getJson("/api/v1/public/booking/availability?service_id={$fixture['service']->id}&professional_id={$professional->id}&date=2026-01-05")
            ->assertStatus(409)
            ->assertJsonPath('code', 'appointment_unavailable')
            ->assertJsonMissing(['professional_id' => $professional->id]);
    }
});

it('omits slots overlapping ProfessionalTimeOff', function (): void {
    $fixture = publicAvailabilityFixture();
    ProfessionalTimeOff::factory()->create([
        'professional_id' => $fixture['professional']->id,
        'starts_at' => '2026-01-05 10:00:00',
        'ends_at' => '2026-01-05 11:00:00',
    ]);

    $slots = $this->getJson("/api/v1/public/booking/availability?service_id={$fixture['service']->id}&professional_id={$fixture['professional']->id}&date=2026-01-05")
        ->assertOk()->json('data.slots');

    expect(collect($slots)->pluck('local_start'))
        ->not->toContain('10:00', '10:15', '10:30', '10:45');
});

it('omits confirmed conflicts and keeps terminal appointments nonblocking', function (): void {
    $fixture = publicAvailabilityFixture();
    $customer = Customer::factory()->create();
    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'service_id' => $fixture['service']->id,
        'professional_id' => $fixture['professional']->id,
        'starts_at' => '2026-01-05 09:00:00',
        'ends_at' => '2026-01-05 09:45:00',
        'duration_minutes' => 45,
        'status' => AppointmentStatus::CONFIRMED,
    ]);

    $slots = $this->getJson("/api/v1/public/booking/availability?service_id={$fixture['service']->id}&professional_id={$fixture['professional']->id}&date=2026-01-05")
        ->assertOk()->json('data.slots');
    expect(collect($slots)->pluck('local_start'))->not->toContain('09:00');

    $appointment->update(['status' => AppointmentStatus::CANCELLED]);
    $slots = $this->getJson("/api/v1/public/booking/availability?service_id={$fixture['service']->id}&professional_id={$fixture['professional']->id}&date=2026-01-05")
        ->assertOk()->json('data.slots');
    expect(collect($slots)->pluck('local_start'))->toContain('09:00');
});

it('omits slots when global capacity is full despite a free selected Professional', function (): void {
    $fixture = publicAvailabilityFixture();
    $fixture['profile']->update(['max_simultaneous_clients' => 1]);
    $otherProfessional = Professional::factory()->create(['active' => true]);
    $otherProfessional->services()->attach($fixture['service']);
    Appointment::factory()->create([
        'customer_id' => Customer::factory()->create()->id,
        'service_id' => $fixture['service']->id,
        'professional_id' => $otherProfessional->id,
        'starts_at' => '2026-01-05 09:00:00',
        'ends_at' => '2026-01-05 09:45:00',
        'duration_minutes' => 45,
        'status' => AppointmentStatus::CONFIRMED,
    ]);

    $slots = $this->getJson("/api/v1/public/booking/availability?service_id={$fixture['service']->id}&professional_id={$fixture['professional']->id}&date=2026-01-05")
        ->assertOk()->json('data.slots');

    expect(collect($slots)->pluck('local_start'))->not->toContain('09:00');
});

it('requires all availability query parameters', function (): void {
    $this->getJson('/api/v1/public/booking/availability')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['service_id', 'professional_id', 'date']);
});

it('rate-limits availability by IP with safe JSON 429', function (): void {
    $fixture = publicAvailabilityFixture('UTC', false);
    $request = fn () => $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.12'])
        ->getJson("/api/v1/public/booking/availability?service_id={$fixture['service']->id}&professional_id={$fixture['professional']->id}&date=2026-01-05");

    foreach (range(1, 30) as $attempt) {
        $request()->assertOk();
    }

    $request()->assertTooManyRequests()->assertJsonStructure(['message'])->assertJsonMissingPath('exception');
});
