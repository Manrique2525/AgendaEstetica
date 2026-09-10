<?php

use App\Actions\CreateAppointment;
use App\Enums\AppointmentHistoryEventType;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\Service;
use App\Models\ServiceCategory;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Tests\Support\Concurrency\ConcurrentProcessRunner;

beforeEach(function (): void {
    expect(config('database.default'))->toBe('mysql')
        ->and(config('database.connections.mysql.database'))->toBe('agenda_estetica_test');
    concurrencyCleanup();
});

afterEach(function (): void {
    concurrencyCleanup();
});

function concurrencyCleanup(): void
{
    DB::table('appointment_histories')->delete();
    DB::table('appointments')->delete();
    DB::table('professional_time_off')->delete();
    DB::table('professional_schedules')->delete();
    DB::table('professional_service')->delete();
    DB::table('customers')->delete();
    DB::table('services')->delete();
    DB::table('service_categories')->delete();
    DB::table('business_hours')->delete();
    DB::table('business_profiles')->delete();
}

function concurrencyFixture(int $capacity = 8): array
{
    $profile = BusinessProfile::factory()->create([
        'timezone' => 'UTC',
        'max_simultaneous_clients' => $capacity,
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
        'duration_minutes' => 60,
    ]);
    $professional = concurrencyProfessional($service);
    $customer = Customer::factory()->create();

    return compact('profile', 'category', 'service', 'professional', 'customer');
}

function concurrencyProfessional(Service $service): Professional
{
    $professional = Professional::factory()->create();
    $professional->services()->attach($service);
    $professional->schedules()->create([
        'weekday' => 1,
        'starts_at' => '09:00',
        'ends_at' => '18:00',
    ]);

    return $professional;
}

function concurrencyPayload(array $fixture, array $overrides = []): array
{
    return array_merge([
        'action' => 'create',
        'customer_id' => $fixture['customer']->id,
        'service_id' => $fixture['service']->id,
        'professional_id' => $fixture['professional']->id,
        'starts_at' => '2026-01-05 10:00:00',
        'ends_at' => '2026-01-05 11:00:00',
    ], $overrides);
}

function concurrencyTime(string $time): CarbonImmutable
{
    return CarbonImmutable::parse("2026-01-05 {$time}", 'UTC');
}

function runConcurrent(array $payloads): array
{
    $run = (new ConcurrentProcessRunner)->start($payloads);
    $run->awaitReady();
    $run->release();

    return $run->wait();
}

function successfulResults(array $results): array
{
    return array_values(array_filter($results, static fn (array $result): bool => $result['success'] === true));
}

function assertDomainRace(array $results, int $successes): void
{
    $failures = array_values(array_filter($results, static fn (array $result): bool => $result['success'] === false));

    expect(successfulResults($results))->toHaveCount($successes);

    foreach ($failures as $failure) {
        expect($failure['exception'])->toBe(InvalidArgumentException::class)
            ->and($failure['sqlstate'] ?? null)->toBeNull();
    }
}

it('blocks CreateAppointment on the BusinessProfile row until release', function (): void {
    $fixture = concurrencyFixture();
    DB::beginTransaction();
    DB::select('SELECT id FROM business_profiles WHERE singleton_key = 1 FOR UPDATE');

    $run = (new ConcurrentProcessRunner)->start([concurrencyPayload($fixture)]);
    $run->awaitReady();
    $run->release();
    usleep(250000);

    expect($run->isComplete())->toBeFalse();
    DB::commit();
    $results = $run->wait();

    expect(successfulResults($results))->toHaveCount(1);
});

it('blocks CreateAppointment on the Professional row after acquiring BusinessProfile', function (): void {
    $fixture = concurrencyFixture();
    DB::beginTransaction();
    DB::select('SELECT id FROM professionals WHERE id = ? FOR UPDATE', [$fixture['professional']->id]);

    $run = (new ConcurrentProcessRunner)->start([concurrencyPayload($fixture)]);
    $run->awaitReady();
    $run->release();
    usleep(250000);

    expect($run->isComplete())->toBeFalse();
    DB::commit();
    expect(successfulResults($run->wait()))->toHaveCount(1);
});

it('blocks CancelAppointment on the Appointment row after resource locks', function (): void {
    $fixture = concurrencyFixture();
    $appointment = (new CreateAppointment)->execute(
        $fixture['customer'],
        $fixture['service'],
        $fixture['professional'],
        CarbonImmutable::parse('2026-01-05 10:00:00', 'UTC'),
        CarbonImmutable::parse('2026-01-05 11:00:00', 'UTC'),
    );
    DB::beginTransaction();
    DB::select('SELECT id FROM appointments WHERE id = ? FOR UPDATE', [$appointment->id]);

    $run = (new ConcurrentProcessRunner)->start([['action' => 'cancel', 'appointment_id' => $appointment->id]]);
    $run->awaitReady();
    $run->release();
    usleep(250000);

    expect($run->isComplete())->toBeFalse();
    DB::commit();
    expect(successfulResults($run->wait()))->toHaveCount(1);
});

it('prevents same-Professional double booking in ten independent races', function (): void {
    for ($iteration = 0; $iteration < 10; $iteration++) {
        concurrencyCleanup();
        $fixture = concurrencyFixture();
        $results = runConcurrent([
            concurrencyPayload($fixture),
            concurrencyPayload($fixture, ['customer_id' => Customer::factory()->create()->id]),
        ]);

        assertDomainRace($results, 1);
        expect(successfulResults($results))->toHaveCount(1)
            ->and(Appointment::query()->count())->toBe(1)
            ->and(Appointment::query()->first()->history()->where('event_type', AppointmentHistoryEventType::CREATED)->count())->toBe(1);
    }
});

it('prevents global capacity oversubscription across Professionals in ten races', function (): void {
    for ($iteration = 0; $iteration < 10; $iteration++) {
        concurrencyCleanup();
        $fixture = concurrencyFixture(1);
        $other = concurrencyProfessional($fixture['service']);
        $results = runConcurrent([
            concurrencyPayload($fixture),
            concurrencyPayload($fixture, [
                'customer_id' => Customer::factory()->create()->id,
                'professional_id' => $other->id,
            ]),
        ]);

        assertDomainRace($results, 1);
        expect(successfulResults($results))->toHaveCount(1)
            ->and(Appointment::query()->count())->toBe(1);
    }
});

it('allows two simultaneous creators when global capacity is two', function (): void {
    $fixture = concurrencyFixture(2);
    $other = concurrencyProfessional($fixture['service']);
    $results = runConcurrent([
        concurrencyPayload($fixture),
        concurrencyPayload($fixture, [
            'customer_id' => Customer::factory()->create()->id,
            'professional_id' => $other->id,
        ]),
    ]);

    assertDomainRace($results, 2);
    expect(successfulResults($results))->toHaveCount(2)
        ->and(Appointment::query()->count())->toBe(2);
});

it('allows only two of three simultaneous creators at capacity two', function (): void {
    $fixture = concurrencyFixture(2);
    $second = concurrencyProfessional($fixture['service']);
    $third = concurrencyProfessional($fixture['service']);
    $results = runConcurrent([
        concurrencyPayload($fixture),
        concurrencyPayload($fixture, ['customer_id' => Customer::factory()->create()->id, 'professional_id' => $second->id]),
        concurrencyPayload($fixture, ['customer_id' => Customer::factory()->create()->id, 'professional_id' => $third->id]),
    ]);

    assertDomainRace($results, 2);
    expect(successfulResults($results))->toHaveCount(2)
        ->and(Appointment::query()->count())->toBe(2);
});

it('serializes same-target reschedules and preserves the losing Appointment', function (): void {
    $fixture = concurrencyFixture();
    $second = concurrencyProfessional($fixture['service']);
    $target = concurrencyProfessional($fixture['service']);
    $firstAppointment = (new CreateAppointment)->execute($fixture['customer'], $fixture['service'], $fixture['professional'], concurrencyTime('10:00:00'), concurrencyTime('11:00:00'));
    $secondAppointment = (new CreateAppointment)->execute(Customer::factory()->create(), $fixture['service'], $second, concurrencyTime('12:00:00'), concurrencyTime('13:00:00'));

    $results = runConcurrent([
        ['action' => 'reschedule', 'appointment_id' => $firstAppointment->id, 'professional_id' => $target->id, 'starts_at' => '2026-01-05 14:00:00', 'ends_at' => '2026-01-05 15:00:00'],
        ['action' => 'reschedule', 'appointment_id' => $secondAppointment->id, 'professional_id' => $target->id, 'starts_at' => '2026-01-05 14:00:00', 'ends_at' => '2026-01-05 15:00:00'],
    ]);

    assertDomainRace($results, 1);
    expect(successfulResults($results))->toHaveCount(1)
        ->and(Appointment::query()->where('professional_id', $target->id)->count())->toBe(1)
        ->and(Appointment::query()->where('professional_id', $target->id)->first()->history()->where('event_type', AppointmentHistoryEventType::RESCHEDULED)->count())->toBe(1);
});

it('completes ten opposite-direction reschedule races without deadlocks', function (): void {
    for ($iteration = 0; $iteration < 10; $iteration++) {
        concurrencyCleanup();
        $fixture = concurrencyFixture();
        $second = concurrencyProfessional($fixture['service']);
        $firstAppointment = (new CreateAppointment)->execute($fixture['customer'], $fixture['service'], $fixture['professional'], concurrencyTime('10:00:00'), concurrencyTime('11:00:00'));
        $secondAppointment = (new CreateAppointment)->execute(Customer::factory()->create(), $fixture['service'], $second, concurrencyTime('12:00:00'), concurrencyTime('13:00:00'));
        $results = runConcurrent([
            ['action' => 'reschedule', 'appointment_id' => $firstAppointment->id, 'professional_id' => $second->id, 'starts_at' => '2026-01-05 14:00:00', 'ends_at' => '2026-01-05 15:00:00'],
            ['action' => 'reschedule', 'appointment_id' => $secondAppointment->id, 'professional_id' => $fixture['professional']->id, 'starts_at' => '2026-01-05 16:00:00', 'ends_at' => '2026-01-05 17:00:00'],
        ]);

        assertDomainRace($results, 2);
    }
});

it('serializes competing terminal transitions', function (): void {
    $fixture = concurrencyFixture();
    $appointment = (new CreateAppointment)->execute($fixture['customer'], $fixture['service'], $fixture['professional'], concurrencyTime('10:00:00'), concurrencyTime('11:00:00'));
    $results = runConcurrent([
        ['action' => 'cancel', 'appointment_id' => $appointment->id],
        ['action' => 'complete', 'appointment_id' => $appointment->id],
    ]);

    assertDomainRace($results, 1);
    expect(successfulResults($results))->toHaveCount(1)
        ->and($appointment->refresh()->status)->toBeIn([AppointmentStatus::CANCELLED, AppointmentStatus::COMPLETED])
        ->and($appointment->history()->where('event_type', AppointmentHistoryEventType::STATUS_CHANGED)->count())->toBe(1);
});

it('serializes cancel versus no-show transitions', function (): void {
    $fixture = concurrencyFixture();
    $appointment = (new CreateAppointment)->execute($fixture['customer'], $fixture['service'], $fixture['professional'], concurrencyTime('10:00:00'), concurrencyTime('11:00:00'));
    $results = runConcurrent([
        ['action' => 'cancel', 'appointment_id' => $appointment->id],
        ['action' => 'no_show', 'appointment_id' => $appointment->id],
    ]);

    assertDomainRace($results, 1);
    expect(successfulResults($results))->toHaveCount(1)
        ->and($appointment->refresh()->status)->toBeIn([AppointmentStatus::CANCELLED, AppointmentStatus::NO_SHOW])
        ->and($appointment->history()->where('event_type', AppointmentHistoryEventType::STATUS_CHANGED)->count())->toBe(1);
});

it('serializes reschedule versus cancel into a valid serial outcome', function (): void {
    $fixture = concurrencyFixture();
    $appointment = (new CreateAppointment)->execute($fixture['customer'], $fixture['service'], $fixture['professional'], concurrencyTime('10:00:00'), concurrencyTime('11:00:00'));
    $results = runConcurrent([
        ['action' => 'cancel', 'appointment_id' => $appointment->id],
        ['action' => 'reschedule', 'appointment_id' => $appointment->id, 'professional_id' => $fixture['professional']->id, 'starts_at' => '2026-01-05 12:00:00', 'ends_at' => '2026-01-05 13:00:00'],
    ]);

    assertDomainRace($results, 1);
    expect(successfulResults($results))->toHaveCount(1)
        ->and($appointment->refresh()->status)->toBeIn([AppointmentStatus::CANCELLED, AppointmentStatus::CONFIRMED]);
});

it('serializes cancellation against a competing create at released capacity', function (): void {
    $fixture = concurrencyFixture(1);
    $appointment = (new CreateAppointment)->execute($fixture['customer'], $fixture['service'], $fixture['professional'], concurrencyTime('10:00:00'), concurrencyTime('11:00:00'));
    $results = runConcurrent([
        ['action' => 'cancel', 'appointment_id' => $appointment->id],
        concurrencyPayload($fixture, ['customer_id' => Customer::factory()->create()->id]),
    ]);
    $successes = successfulResults($results);

    expect(count($successes))->toBeIn([1, 2]);
    assertDomainRace($results, count($successes));

    if (count($successes) === 2) {
        expect($appointment->refresh()->status)->toBe(AppointmentStatus::CANCELLED)
            ->and(Appointment::query()->where('status', AppointmentStatus::CONFIRMED)->count())->toBe(1);
    } else {
        expect($appointment->refresh()->status)->toBe(AppointmentStatus::CANCELLED)
            ->and(Appointment::query()->count())->toBe(1)
            ->and(array_values(array_filter($results, static fn (array $result): bool => $result['success'] === false)))->toHaveCount(1);
    }
});

it('serializes completion against a competing create at released capacity', function (): void {
    $fixture = concurrencyFixture(1);
    $appointment = (new CreateAppointment)->execute($fixture['customer'], $fixture['service'], $fixture['professional'], concurrencyTime('10:00:00'), concurrencyTime('11:00:00'));
    $results = runConcurrent([
        ['action' => 'complete', 'appointment_id' => $appointment->id],
        concurrencyPayload($fixture, ['customer_id' => Customer::factory()->create()->id]),
    ]);
    $successes = successfulResults($results);

    expect(count($successes))->toBeIn([1, 2]);
    assertDomainRace($results, count($successes));

    if (count($successes) === 2) {
        expect($appointment->refresh()->status)->toBe(AppointmentStatus::COMPLETED)
            ->and(Appointment::query()->where('status', AppointmentStatus::CONFIRMED)->count())->toBe(1);
    } else {
        expect($appointment->refresh()->status)->toBe(AppointmentStatus::COMPLETED)
            ->and(Appointment::query()->count())->toBe(1)
            ->and(array_values(array_filter($results, static fn (array $result): bool => $result['success'] === false)))->toHaveCount(1);
    }
});

it('coordinates BusinessHours replacement before CreateAppointment validation', function (): void {
    $fixture = concurrencyFixture();
    DB::beginTransaction();
    DB::select('SELECT id FROM business_profiles WHERE singleton_key = 1 FOR UPDATE');
    DB::table('business_hours')->where('business_profile_id', $fixture['profile']->id)->delete();
    DB::table('business_hours')->insert(['business_profile_id' => $fixture['profile']->id, 'weekday' => 1, 'interval_order' => 1, 'opens_at' => '12:00', 'closes_at' => '18:00']);

    $run = (new ConcurrentProcessRunner)->start([concurrencyPayload($fixture)]);
    $run->awaitReady();
    $run->release();
    usleep(250000);
    expect($run->isComplete())->toBeFalse();
    DB::commit();
    $result = $run->wait()[0];

    expect($result['success'])->toBeFalse()
        ->and($result['exception'])->toBe(InvalidArgumentException::class)
        ->and($result['sqlstate'] ?? null)->toBeNull();
});

it('coordinates ProfessionalSchedule replacement before CreateAppointment validation', function (): void {
    $fixture = concurrencyFixture();
    DB::beginTransaction();
    DB::select('SELECT id FROM professionals WHERE id = ? FOR UPDATE', [$fixture['professional']->id]);
    DB::table('professional_schedules')->where('professional_id', $fixture['professional']->id)->delete();
    DB::table('professional_schedules')->insert(['professional_id' => $fixture['professional']->id, 'weekday' => 1, 'starts_at' => '12:00', 'ends_at' => '18:00']);

    $run = (new ConcurrentProcessRunner)->start([concurrencyPayload($fixture)]);
    $run->awaitReady();
    $run->release();
    usleep(250000);
    expect($run->isComplete())->toBeFalse();
    DB::commit();
    $result = $run->wait()[0];

    expect($result['success'])->toBeFalse()
        ->and($result['exception'])->toBe(InvalidArgumentException::class)
        ->and($result['sqlstate'] ?? null)->toBeNull();
});

it('observes committed ProfessionalSchedule changes after waiting before Reschedule', function (): void {
    $fixture = concurrencyFixture();
    $target = concurrencyProfessional($fixture['service']);
    $appointment = (new CreateAppointment)->execute($fixture['customer'], $fixture['service'], $fixture['professional'], concurrencyTime('10:00:00'), concurrencyTime('11:00:00'));
    DB::beginTransaction();
    DB::select('SELECT id FROM professionals WHERE id = ? FOR UPDATE', [$target->id]);
    DB::table('professional_schedules')->where('professional_id', $target->id)->delete();
    DB::table('professional_schedules')->insert(['professional_id' => $target->id, 'weekday' => 1, 'starts_at' => '12:00', 'ends_at' => '18:00']);

    $run = (new ConcurrentProcessRunner)->start([[
        'action' => 'reschedule',
        'appointment_id' => $appointment->id,
        'professional_id' => $target->id,
        'starts_at' => '2026-01-05 10:00:00',
        'ends_at' => '2026-01-05 11:00:00',
    ]]);
    $run->awaitReady();
    $run->release();
    usleep(250000);
    expect($run->isComplete())->toBeFalse();
    DB::commit();
    $result = $run->wait()[0];

    expect($result['success'])->toBeFalse()
        ->and($result['exception'])->toBe(InvalidArgumentException::class)
        ->and($result['sqlstate'] ?? null)->toBeNull()
        ->and($appointment->refresh()->professional_id)->toBe($fixture['professional']->id);
});
