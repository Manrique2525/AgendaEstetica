<?php

use App\Actions\CheckAppointmentAvailability;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\BusinessProfile;
use App\Models\Professional;
use App\Models\Service;
use App\Models\ServiceCategory;
use Carbon\CarbonImmutable;

function availabilityFixture(array $overrides = []): array
{
    $profile = BusinessProfile::factory()->create([
        'timezone' => $overrides['timezone'] ?? 'UTC',
        'max_simultaneous_clients' => $overrides['capacity'] ?? 8,
    ]);
    $profile->hours()->create([
        'weekday' => 1,
        'interval_order' => 1,
        'opens_at' => '09:00',
        'closes_at' => '18:00',
    ]);

    $category = ServiceCategory::factory()->create();
    $service = Service::factory()->create([
        'service_category_id' => $category->id,
        'duration_minutes' => $overrides['duration'] ?? 60,
    ]);
    $professional = Professional::factory()->create();
    $professional->schedules()->create([
        'weekday' => 1,
        'starts_at' => '09:00',
        'ends_at' => '18:00',
    ]);
    $professional->services()->attach($service);

    return compact('profile', 'category', 'service', 'professional');
}

function utcTime(string $time): CarbonImmutable
{
    return CarbonImmutable::parse("2026-01-05 {$time}", 'UTC');
}

it('accepts a valid requested interval for a specific Professional', function (): void {
    $fixture = availabilityFixture();

    expect((new CheckAppointmentAvailability)->execute(
        $fixture['service'],
        $fixture['professional'],
        utcTime('10:00:00'),
        utcTime('11:00:00'),
    ))->toBeTrue();
});

it('rejects invalid interval, duration, active-state and compatibility conditions', function (): void {
    $fixture = availabilityFixture();
    $checker = new CheckAppointmentAvailability;

    expect($checker->execute($fixture['service'], $fixture['professional'], utcTime('11:00:00'), utcTime('10:00:00')))
        ->toBeFalse()
        ->and($checker->execute($fixture['service'], $fixture['professional'], utcTime('10:00:00'), utcTime('10:30:00')))
        ->toBeFalse();

    $fixture['category']->update(['active' => false]);
    expect($checker->execute($fixture['service'], $fixture['professional'], utcTime('10:00:00'), utcTime('11:00:00')))
        ->toBeFalse();

    $fixture['category']->update(['active' => true]);
    $fixture['service']->update(['active' => false]);
    expect($checker->execute($fixture['service'], $fixture['professional'], utcTime('10:00:00'), utcTime('11:00:00')))
        ->toBeFalse();

    $fixture['service']->update(['active' => true]);
    $fixture['professional']->update(['active' => false]);
    expect($checker->execute($fixture['service'], $fixture['professional'], utcTime('10:00:00'), utcTime('11:00:00')))
        ->toBeFalse();

    $fixture['professional']->update(['active' => true]);
    $fixture['professional']->services()->detach($fixture['service']);
    expect($checker->execute($fixture['service'], $fixture['professional'], utcTime('10:00:00'), utcTime('11:00:00')))
        ->toBeFalse();
});

it('requires full BusinessHours and ProfessionalSchedule coverage', function (): void {
    $fixture = availabilityFixture(['duration' => 120]);
    $fixture['profile']->hours()->delete();
    $fixture['profile']->hours()->createMany([
        ['weekday' => 1, 'interval_order' => 1, 'opens_at' => '09:00', 'closes_at' => '12:00'],
        ['weekday' => 1, 'interval_order' => 2, 'opens_at' => '13:00', 'closes_at' => '18:00'],
    ]);
    $fixture['professional']->schedules()->delete();
    $fixture['professional']->schedules()->createMany([
        ['weekday' => 1, 'starts_at' => '09:00', 'ends_at' => '12:00'],
        ['weekday' => 1, 'starts_at' => '13:00', 'ends_at' => '18:00'],
    ]);

    $checker = new CheckAppointmentAvailability;

    expect($checker->execute($fixture['service'], $fixture['professional'], utcTime('10:00:00'), utcTime('12:00:00')))
        ->toBeTrue()
        ->and($checker->execute($fixture['service'], $fixture['professional'], utcTime('11:00:00'), utcTime('13:00:00')))
        ->toBeFalse()
        ->and($checker->execute($fixture['service'], $fixture['professional'], utcTime('12:00:00'), utcTime('14:00:00')))
        ->toBeFalse();
});

it('merges adjacent BusinessHours and ProfessionalSchedule intervals without bridging gaps', function (): void {
    $fixture = availabilityFixture(['duration' => 120]);
    $fixture['profile']->hours()->delete();
    $fixture['profile']->hours()->createMany([
        ['weekday' => 1, 'interval_order' => 1, 'opens_at' => '09:00', 'closes_at' => '13:00'],
        ['weekday' => 1, 'interval_order' => 2, 'opens_at' => '13:00', 'closes_at' => '17:00'],
    ]);
    $fixture['professional']->schedules()->delete();
    $fixture['professional']->schedules()->createMany([
        ['weekday' => 1, 'starts_at' => '09:00', 'ends_at' => '13:00'],
        ['weekday' => 1, 'starts_at' => '13:00', 'ends_at' => '17:00'],
    ]);

    $checker = new CheckAppointmentAvailability;

    expect($checker->execute($fixture['service'], $fixture['professional'], utcTime('12:00:00'), utcTime('14:00:00')))
        ->toBeTrue();

    $fixture['profile']->hours()->where('weekday', 1)->delete();
    $fixture['profile']->hours()->createMany([
        ['weekday' => 1, 'interval_order' => 1, 'opens_at' => '09:00', 'closes_at' => '13:00'],
        ['weekday' => 1, 'interval_order' => 2, 'opens_at' => '14:00', 'closes_at' => '17:00'],
    ]);
    $fixture['professional']->schedules()->delete();
    $fixture['professional']->schedules()->createMany([
        ['weekday' => 1, 'starts_at' => '09:00', 'ends_at' => '13:00'],
        ['weekday' => 1, 'starts_at' => '14:00', 'ends_at' => '17:00'],
    ]);

    expect($checker->execute($fixture['service'], $fixture['professional'], utcTime('12:00:00'), utcTime('15:00:00')))
        ->toBeFalse();
});

it('rejects ProfessionalTimeOff overlap and allows adjacency', function (): void {
    $fixture = availabilityFixture();
    $fixture['professional']->timeOff()->create([
        'starts_at' => '2026-01-05 12:00:00',
        'ends_at' => '2026-01-05 13:00:00',
    ]);
    $checker = new CheckAppointmentAvailability;

    expect($checker->execute($fixture['service'], $fixture['professional'], utcTime('11:30:00'), utcTime('12:30:00')))
        ->toBeFalse()
        ->and($checker->execute($fixture['service'], $fixture['professional'], utcTime('13:00:00'), utcTime('14:00:00')))
        ->toBeTrue();
});

it('rejects confirmed Professional overlap but ignores terminal appointments', function (): void {
    $fixture = availabilityFixture();
    $checker = new CheckAppointmentAvailability;

    Appointment::factory()->create([
        'professional_id' => $fixture['professional']->id,
        'service_id' => $fixture['service']->id,
        'status' => AppointmentStatus::CONFIRMED,
        'starts_at' => '2026-01-05 10:00:00',
        'ends_at' => '2026-01-05 11:00:00',
        'duration_minutes' => 60,
    ]);

    expect($checker->execute($fixture['service'], $fixture['professional'], utcTime('10:30:00'), utcTime('11:30:00')))
        ->toBeFalse();

    Appointment::query()->delete();

    foreach ([AppointmentStatus::CANCELLED, AppointmentStatus::COMPLETED, AppointmentStatus::NO_SHOW] as $status) {
        Appointment::factory()->create([
            'professional_id' => $fixture['professional']->id,
            'service_id' => $fixture['service']->id,
            'status' => $status,
            'starts_at' => '2026-01-05 10:00:00',
            'ends_at' => '2026-01-05 11:00:00',
            'duration_minutes' => 60,
        ]);

        expect($checker->execute($fixture['service'], $fixture['professional'], utcTime('10:30:00'), utcTime('11:30:00')))
            ->toBeTrue();
        Appointment::query()->delete();
    }
});

it('calculates global capacity by peak concurrency rather than overlapping row count', function (): void {
    $fixture = availabilityFixture(['capacity' => 2, 'duration' => 120]);
    $otherOne = Professional::factory()->create();
    $otherTwo = Professional::factory()->create();
    $otherThree = Professional::factory()->create();
    $otherOne->services()->attach($fixture['service']);
    $otherTwo->services()->attach($fixture['service']);
    $otherThree->services()->attach($fixture['service']);

    foreach ([$otherOne, $otherTwo] as $index => $professional) {
        Appointment::factory()->create([
            'professional_id' => $professional->id,
            'service_id' => $fixture['service']->id,
            'status' => AppointmentStatus::CONFIRMED,
            'starts_at' => $index === 0 ? '2026-01-05 10:00:00' : '2026-01-05 12:00:00',
            'ends_at' => $index === 0 ? '2026-01-05 11:00:00' : '2026-01-05 13:00:00',
            'duration_minutes' => 60,
        ]);
    }

    $checker = new CheckAppointmentAvailability;

    expect($checker->execute($fixture['service'], $fixture['professional'], utcTime('10:30:00'), utcTime('12:30:00')))
        ->toBeTrue();

    Appointment::factory()->create([
        'professional_id' => $otherThree->id,
        'service_id' => $fixture['service']->id,
        'status' => AppointmentStatus::CONFIRMED,
        'starts_at' => '2026-01-05 10:45:00',
        'ends_at' => '2026-01-05 11:15:00',
        'duration_minutes' => 30,
    ]);

    expect($checker->execute($fixture['service'], $fixture['professional'], utcTime('10:30:00'), utcTime('12:30:00')))
        ->toBeFalse();
});

it('processes capacity end events before start events at the same instant', function (): void {
    $fixture = availabilityFixture(['capacity' => 1]);
    $other = Professional::factory()->create();
    $other->services()->attach($fixture['service']);
    Appointment::factory()->create([
        'professional_id' => $other->id,
        'service_id' => $fixture['service']->id,
        'status' => AppointmentStatus::CONFIRMED,
        'starts_at' => '2026-01-05 09:00:00',
        'ends_at' => '2026-01-05 10:00:00',
        'duration_minutes' => 60,
    ]);

    expect((new CheckAppointmentAvailability)->execute(
        $fixture['service'],
        $fixture['professional'],
        utcTime('10:00:00'),
        utcTime('11:00:00'),
    ))->toBeTrue();
});

it('rejects nonexistent and ambiguous recurring local boundaries', function (): void {
    $gap = availabilityFixture(['timezone' => 'America/New_York', 'duration' => 30]);
    $gap['profile']->hours()->delete();
    $gap['profile']->hours()->create([
        'weekday' => 7,
        'interval_order' => 1,
        'opens_at' => '09:00',
        'closes_at' => '18:00',
    ]);
    $gap['professional']->schedules()->delete();
    $gap['professional']->schedules()->create([
        'weekday' => 7,
        'starts_at' => '09:00',
        'ends_at' => '18:00',
    ]);
    $checker = new CheckAppointmentAvailability;

    expect($checker->execute(
        $gap['service'],
        $gap['professional'],
        CarbonImmutable::parse('2026-07-05 14:00:00', 'UTC'),
        CarbonImmutable::parse('2026-07-05 14:30:00', 'UTC'),
    ))->toBeTrue();

    $gap['service']->update(['duration_minutes' => 60]);
    $gap['profile']->hours()->delete();
    $gap['profile']->hours()->create([
        'weekday' => 7,
        'interval_order' => 1,
        'opens_at' => '01:00',
        'closes_at' => '04:00',
    ]);
    $gap['professional']->schedules()->delete();
    $gap['professional']->schedules()->create([
        'weekday' => 7,
        'starts_at' => '01:00',
        'ends_at' => '04:00',
    ]);

    expect($checker->execute(
        $gap['service'],
        $gap['professional'],
        CarbonImmutable::parse('2026-03-08 06:30:00', 'UTC'),
        CarbonImmutable::parse('2026-03-08 07:30:00', 'UTC'),
    ))->toBeTrue();

    $gap['profile']->hours()->delete();
    $gap['profile']->hours()->create([
        'weekday' => 7,
        'interval_order' => 1,
        'opens_at' => '02:30',
        'closes_at' => '04:00',
    ]);
    $gap['professional']->schedules()->delete();
    $gap['professional']->schedules()->create([
        'weekday' => 7,
        'starts_at' => '02:30',
        'ends_at' => '04:00',
    ]);

    $checker = new CheckAppointmentAvailability;

    expect($checker->execute(
        $gap['service'],
        $gap['professional'],
        CarbonImmutable::parse('2026-03-08 07:30:00', 'UTC'),
        CarbonImmutable::parse('2026-03-08 08:30:00', 'UTC'),
    ))->toBeFalse();

    $fold = $gap;
    $fold['profile']->hours()->delete();
    $fold['profile']->hours()->create([
        'weekday' => 7,
        'interval_order' => 1,
        'opens_at' => '01:30',
        'closes_at' => '02:30',
    ]);
    $fold['professional']->schedules()->delete();
    $fold['professional']->schedules()->create([
        'weekday' => 7,
        'starts_at' => '01:30',
        'ends_at' => '02:30',
    ]);

    expect($checker->execute(
        $fold['service'],
        $fold['professional'],
        CarbonImmutable::parse('2026-11-01 05:30:00', 'UTC'),
        CarbonImmutable::parse('2026-11-01 06:30:00', 'UTC'),
    ))->toBeFalse();
});

it('does not mutate persistence while checking availability', function (): void {
    $fixture = availabilityFixture();
    $before = [
        'appointments' => Appointment::query()->count(),
        'histories' => DB::table('appointment_histories')->count(),
        'schedules' => $fixture['professional']->schedules()->count(),
        'time_off' => $fixture['professional']->timeOff()->count(),
    ];

    expect((new CheckAppointmentAvailability)->execute(
        $fixture['service'],
        $fixture['professional'],
        utcTime('10:00:00'),
        utcTime('11:00:00'),
    ))->toBeTrue()
        ->and([
            'appointments' => Appointment::query()->count(),
            'histories' => DB::table('appointment_histories')->count(),
            'schedules' => $fixture['professional']->schedules()->count(),
            'time_off' => $fixture['professional']->timeOff()->count(),
        ])->toBe($before);
});
