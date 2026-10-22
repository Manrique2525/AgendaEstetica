<?php

use App\Actions\CancelAppointment;
use App\Actions\CompleteAppointment;
use App\Actions\CreateAppointment;
use App\Actions\MarkAppointmentNoShow;
use App\Actions\RescheduleAppointment;
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

function appointmentOperationFixture(int $capacity = 8): array
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
    $professional = Professional::factory()->create();
    $professional->services()->attach($service);
    $professional->schedules()->create([
        'weekday' => 1,
        'starts_at' => '09:00',
        'ends_at' => '18:00',
    ]);

    return [
        'profile' => $profile,
        'category' => $category,
        'service' => $service,
        'professional' => $professional,
        'customer' => Customer::factory()->create(),
    ];
}

function operationTime(string $time): CarbonImmutable
{
    return CarbonImmutable::parse("2026-01-05 {$time}", 'UTC');
}

it('creates a confirmed Appointment with a created history row atomically', function (): void {
    $fixture = appointmentOperationFixture();

    $appointment = (new CreateAppointment)->execute(
        $fixture['customer'],
        $fixture['service'],
        $fixture['professional'],
        operationTime('10:00:00'),
        operationTime('11:00:00'),
    );

    expect($appointment->status)->toBe(AppointmentStatus::CONFIRMED)
        ->and($appointment->duration_minutes)->toBe(60)
        ->and($appointment->history)->toHaveCount(1)
        ->and($appointment->history->first()->event_type)->toBe(AppointmentHistoryEventType::CREATED)
        ->and($appointment->history->first()->to_status)->toBe(AppointmentStatus::CONFIRMED);
});

it('re-reads Service duration before creating an Appointment', function (): void {
    $fixture = appointmentOperationFixture();
    $staleService = $fixture['service']->fresh();
    $fixture['service']->update(['duration_minutes' => 90]);

    $appointment = (new CreateAppointment)->execute(
        $fixture['customer'],
        $staleService,
        $fixture['professional'],
        operationTime('10:00:00'),
        operationTime('11:30:00'),
    );

    expect($appointment->duration_minutes)->toBe(90);
});

it('rejects unavailable creation without partial Appointment or history persistence', function (): void {
    $fixture = appointmentOperationFixture();
    $create = new CreateAppointment;

    $create->execute(
        $fixture['customer'],
        $fixture['service'],
        $fixture['professional'],
        operationTime('10:00:00'),
        operationTime('11:00:00'),
    );

    expect(fn () => $create->execute(
        Customer::factory()->create(),
        $fixture['service'],
        $fixture['professional'],
        operationTime('10:30:00'),
        operationTime('11:30:00'),
    ))->toThrow(InvalidArgumentException::class)
        ->and(Appointment::query()->count())->toBe(1)
        ->and(DB::table('appointment_histories')->count())->toBe(1);
});

it('reschedules a confirmed Appointment and records the old/new interval', function (): void {
    $fixture = appointmentOperationFixture();
    $appointment = (new CreateAppointment)->execute(
        $fixture['customer'],
        $fixture['service'],
        $fixture['professional'],
        operationTime('10:00:00'),
        operationTime('11:00:00'),
    );

    $rescheduled = (new RescheduleAppointment)->execute(
        $appointment,
        $fixture['professional'],
        operationTime('12:00:00'),
        operationTime('13:00:00'),
    );

    $history = $rescheduled->history()->where('event_type', AppointmentHistoryEventType::RESCHEDULED)->firstOrFail();

    expect($rescheduled->status)->toBe(AppointmentStatus::CONFIRMED)
        ->and($rescheduled->duration_minutes)->toBe(60)
        ->and($rescheduled->starts_at->format('H:i'))->toBe('12:00')
        ->and($history->old_starts_at->format('H:i'))->toBe('10:00')
        ->and($history->new_starts_at->format('H:i'))->toBe('12:00');
});

it('reschedules to another Professional with deterministic target references', function (): void {
    $fixture = appointmentOperationFixture();
    $newProfessional = Professional::factory()->create();
    $newProfessional->services()->attach($fixture['service']);
    $newProfessional->schedules()->create([
        'weekday' => 1,
        'starts_at' => '09:00',
        'ends_at' => '18:00',
    ]);
    $appointment = (new CreateAppointment)->execute(
        $fixture['customer'],
        $fixture['service'],
        $fixture['professional'],
        operationTime('10:00:00'),
        operationTime('11:00:00'),
    );

    $rescheduled = (new RescheduleAppointment)->execute(
        $appointment,
        $newProfessional,
        operationTime('12:00:00'),
        operationTime('13:00:00'),
    );
    $history = $rescheduled->history()->where('event_type', AppointmentHistoryEventType::RESCHEDULED)->firstOrFail();

    expect($rescheduled->professional_id)->toBe($newProfessional->id)
        ->and($history->old_professional_id)->toBe($fixture['professional']->id)
        ->and($history->new_professional_id)->toBe($newProfessional->id);
});

it('preserves the historical duration snapshot during reschedule', function (): void {
    $fixture = appointmentOperationFixture();
    $appointment = (new CreateAppointment)->execute(
        $fixture['customer'],
        $fixture['service'],
        $fixture['professional'],
        operationTime('10:00:00'),
        operationTime('11:00:00'),
    );
    $fixture['service']->update(['duration_minutes' => 90]);

    $rescheduled = (new RescheduleAppointment)->execute(
        $appointment,
        $fixture['professional'],
        operationTime('12:00:00'),
        operationTime('13:00:00'),
    );

    expect($rescheduled->duration_minutes)->toBe(60);
});

it('rejects unavailable reschedule without changing Appointment or history', function (): void {
    $fixture = appointmentOperationFixture();
    $appointment = (new CreateAppointment)->execute(
        $fixture['customer'],
        $fixture['service'],
        $fixture['professional'],
        operationTime('10:00:00'),
        operationTime('11:00:00'),
    );
    Appointment::factory()->create([
        'professional_id' => $fixture['professional']->id,
        'service_id' => $fixture['service']->id,
        'status' => AppointmentStatus::CONFIRMED,
        'starts_at' => '2026-01-05 11:00:00',
        'ends_at' => '2026-01-05 12:00:00',
        'duration_minutes' => 60,
    ]);
    $historyCount = $appointment->history()->count();

    expect(fn () => (new RescheduleAppointment)->execute(
        $appointment,
        $fixture['professional'],
        operationTime('10:30:00'),
        operationTime('11:30:00'),
    ))->toThrow(InvalidArgumentException::class);

    expect($appointment->refresh()->starts_at->format('H:i'))->toBe('10:00')
        ->and($appointment->history()->count())->toBe($historyCount);
});

it('treats rescheduling to the same interval as a no-op', function (): void {
    $fixture = appointmentOperationFixture();
    $appointment = (new CreateAppointment)->execute(
        $fixture['customer'],
        $fixture['service'],
        $fixture['professional'],
        operationTime('10:00:00'),
        operationTime('11:00:00'),
    );

    $same = (new RescheduleAppointment)->execute(
        $appointment,
        $fixture['professional'],
        operationTime('10:00:00'),
        operationTime('11:00:00'),
    );

    expect($same->history()->where('event_type', AppointmentHistoryEventType::RESCHEDULED)->count())->toBe(0)
        ->and($same->starts_at->format('H:i'))->toBe('10:00');
});

it('rejects rescheduling terminal appointments', function (): void {
    $fixture = appointmentOperationFixture();

    foreach ([AppointmentStatus::CANCELLED, AppointmentStatus::COMPLETED, AppointmentStatus::NO_SHOW] as $status) {
        $appointment = (new CreateAppointment)->execute(
            $fixture['customer'],
            $fixture['service'],
            $fixture['professional'],
            operationTime('10:00:00'),
            operationTime('11:00:00'),
        );
        $appointment->update(['status' => $status]);
        $historyCount = $appointment->history()->count();

        expect(fn () => (new RescheduleAppointment)->execute(
            $appointment,
            $fixture['professional'],
            operationTime('12:00:00'),
            operationTime('13:00:00'),
        ))->toThrow(InvalidArgumentException::class);
        expect($appointment->refresh()->status)->toBe($status)
            ->and($appointment->history()->count())->toBe($historyCount);
    }
});

it('transitions confirmed appointments with explicit history Actions', function (): void {
    $fixture = appointmentOperationFixture();
    $actions = [
        [new CancelAppointment, AppointmentStatus::CANCELLED],
        [new CompleteAppointment, AppointmentStatus::COMPLETED],
        [new MarkAppointmentNoShow, AppointmentStatus::NO_SHOW],
    ];

    foreach ($actions as [$action, $expectedStatus]) {
        $appointment = (new CreateAppointment)->execute(
            $fixture['customer'],
            $fixture['service'],
            $fixture['professional'],
            operationTime('10:00:00'),
            operationTime('11:00:00'),
        );

        $updated = $action->execute($appointment);
        $history = $updated->history()->where('event_type', AppointmentHistoryEventType::STATUS_CHANGED)->firstOrFail();

        expect($updated->status)->toBe($expectedStatus)
            ->and($history->from_status)->toBe(AppointmentStatus::CONFIRMED)
            ->and($history->to_status)->toBe($expectedStatus);
    }
});

it('releases Professional and capacity occupancy after cancellation', function (): void {
    $fixture = appointmentOperationFixture(1);
    $create = new CreateAppointment;
    $first = $create->execute($fixture['customer'], $fixture['service'], $fixture['professional'], operationTime('10:00:00'), operationTime('11:00:00'));

    expect(fn () => $create->execute(Customer::factory()->create(), $fixture['service'], $fixture['professional'], operationTime('10:00:00'), operationTime('11:00:00')))
        ->toThrow(InvalidArgumentException::class);

    (new CancelAppointment)->execute($first);

    expect($create->execute(Customer::factory()->create(), $fixture['service'], $fixture['professional'], operationTime('10:00:00'), operationTime('11:00:00'))->status)
        ->toBe(AppointmentStatus::CONFIRMED);
});

it('preserves lifecycle history ordering', function (): void {
    $fixture = appointmentOperationFixture();
    $appointment = (new CreateAppointment)->execute($fixture['customer'], $fixture['service'], $fixture['professional'], operationTime('10:00:00'), operationTime('11:00:00'));
    (new RescheduleAppointment)->execute($appointment, $fixture['professional'], operationTime('12:00:00'), operationTime('13:00:00'));
    (new CancelAppointment)->execute($appointment);

    expect($appointment->history()->orderBy('id')->pluck('event_type')->map->value->all())->toBe([
        AppointmentHistoryEventType::CREATED->value,
        AppointmentHistoryEventType::RESCHEDULED->value,
        AppointmentHistoryEventType::STATUS_CHANGED->value,
    ]);
});
