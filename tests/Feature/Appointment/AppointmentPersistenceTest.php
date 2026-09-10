<?php

use App\Enums\AppointmentHistoryEventType;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentHistory;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\Service;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('persists every appointment status and related core records', function (): void {
    foreach (AppointmentStatus::cases() as $status) {
        $appointment = Appointment::factory()->create(['status' => $status]);

        expect($appointment->refresh()->status)->toBe($status)
            ->and($appointment->customer)->toBeInstanceOf(Customer::class)
            ->and($appointment->service)->toBeInstanceOf(Service::class)
            ->and($appointment->professional)->toBeInstanceOf(Professional::class);
    }
});

it('enforces appointment temporal duration and status checks in MySQL', function (): void {
    $appointment = Appointment::factory()->make();
    $attributes = $appointment->getAttributes();

    expect(fn () => DB::table('appointments')->insert([
        ...$attributes,
        'starts_at' => '2026-01-15 17:00:00',
        'ends_at' => '2026-01-15 17:00:00',
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('appointments')->insert([
        ...$attributes,
        'duration_minutes' => 0,
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('appointments')->insert([
        ...$attributes,
        'duration_minutes' => -1,
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('appointments')->insert([
        ...$attributes,
        'status' => 'unknown',
    ]))->toThrow(QueryException::class);
});

it('preserves the historical duration snapshot when Service changes', function (): void {
    $service = Service::factory()->create(['duration_minutes' => 60]);
    $appointment = Appointment::factory()->create([
        'service_id' => $service->id,
        'duration_minutes' => 60,
    ]);

    $service->update(['duration_minutes' => 90]);

    expect($appointment->refresh()->duration_minutes)->toBe(60)
        ->and($appointment->starts_at->format('Y-m-d H:i:s'))->toBe('2026-01-15 16:00:00')
        ->and($appointment->ends_at->format('Y-m-d H:i:s'))->toBe('2026-01-15 17:00:00');
});

it('restricts deletion of Core resources referenced by an Appointment', function (): void {
    $appointment = Appointment::factory()->create();

    expect(fn () => $appointment->customer->delete())->toThrow(QueryException::class)
        ->and(fn () => $appointment->service->delete())->toThrow(QueryException::class)
        ->and(fn () => $appointment->professional->delete())->toThrow(QueryException::class);

    expect($appointment->refresh()->exists)->toBeTrue();
});

it('persists focused history events and nullable temporal/status fields', function (): void {
    $appointment = Appointment::factory()->create();
    $professional = $appointment->professional;

    $created = AppointmentHistory::factory()->create([
        'appointment_id' => $appointment->id,
        'event_type' => AppointmentHistoryEventType::CREATED,
        'to_status' => AppointmentStatus::CONFIRMED,
        'new_starts_at' => $appointment->starts_at,
        'new_ends_at' => $appointment->ends_at,
        'new_professional_id' => $professional->id,
    ]);
    $rescheduled = AppointmentHistory::factory()->create([
        'appointment_id' => $appointment->id,
        'event_type' => AppointmentHistoryEventType::RESCHEDULED,
        'from_status' => AppointmentStatus::CONFIRMED,
        'to_status' => AppointmentStatus::CONFIRMED,
        'old_starts_at' => '2026-01-15 16:00:00',
        'old_ends_at' => '2026-01-15 17:00:00',
        'new_starts_at' => '2026-01-15 18:00:00',
        'new_ends_at' => '2026-01-15 19:00:00',
        'old_professional_id' => $professional->id,
        'new_professional_id' => $professional->id,
    ]);
    $statusChanged = AppointmentHistory::factory()->create([
        'appointment_id' => $appointment->id,
        'event_type' => AppointmentHistoryEventType::STATUS_CHANGED,
        'from_status' => AppointmentStatus::CONFIRMED,
        'to_status' => AppointmentStatus::COMPLETED,
    ]);

    expect($created->refresh()->event_type)->toBe(AppointmentHistoryEventType::CREATED)
        ->and($created->to_status)->toBe(AppointmentStatus::CONFIRMED)
        ->and($created->newProfessional->is($professional))->toBeTrue()
        ->and($rescheduled->refresh()->event_type)->toBe(AppointmentHistoryEventType::RESCHEDULED)
        ->and($rescheduled->old_starts_at->format('Y-m-d H:i:s'))->toBe('2026-01-15 16:00:00')
        ->and($statusChanged->refresh()->event_type)->toBe(AppointmentHistoryEventType::STATUS_CHANGED)
        ->and($statusChanged->to_status)->toBe(AppointmentStatus::COMPLETED)
        ->and($appointment->history()->count())->toBe(3);
});

it('rejects unknown history events and non-null status values', function (): void {
    $appointment = Appointment::factory()->create();
    $attributes = AppointmentHistory::factory()->make(['appointment_id' => $appointment->id])->getAttributes();

    expect(fn () => DB::table('appointment_histories')->insert([
        ...$attributes,
        'event_type' => 'unknown',
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('appointment_histories')->insert([
        ...$attributes,
        'to_status' => 'unknown',
    ]))->toThrow(QueryException::class);
});

it('prevents deleting an Appointment that has history', function (): void {
    $appointment = Appointment::factory()->create();
    AppointmentHistory::factory()->create(['appointment_id' => $appointment->id]);

    expect(fn () => $appointment->delete())->toThrow(QueryException::class)
        ->and($appointment->refresh()->exists)->toBeTrue();
});

it('keeps the Checkpoint A schema limited to approved tables and fields', function (): void {
    expect(Schema::getColumnListing('appointments'))->toBe([
        'id',
        'customer_id',
        'service_id',
        'professional_id',
        'starts_at',
        'ends_at',
        'duration_minutes',
        'status',
        'created_at',
        'updated_at',
    ])->and(Schema::getColumnListing('appointment_histories'))->toBe([
        'id',
        'appointment_id',
        'event_type',
        'from_status',
        'to_status',
        'old_starts_at',
        'old_ends_at',
        'new_starts_at',
        'new_ends_at',
        'old_professional_id',
        'new_professional_id',
        'created_at',
    ]);
});
