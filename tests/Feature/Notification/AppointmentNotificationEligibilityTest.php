<?php

use App\Actions\DetermineAppointmentNotificationEligibility;
use App\Enums\AppointmentHistoryEventType;
use App\Enums\AppointmentStatus;
use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\Appointment;
use App\Models\AppointmentHistory;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\Service;
use App\Models\ServiceCategory;
use Carbon\CarbonImmutable;

function notificationEligibilityFixture(string $timezone = 'UTC'): array
{
    $profile = BusinessProfile::factory()->create(['timezone' => $timezone]);
    $category = ServiceCategory::factory()->create(['active' => true]);
    $service = Service::factory()->create(['service_category_id' => $category->id, 'active' => true]);
    $professional = Professional::factory()->create(['active' => true]);
    $professional->services()->attach($service);
    $customer = Customer::factory()->create();
    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'professional_id' => $professional->id,
        'starts_at' => '2026-01-05 12:00:00',
        'ends_at' => '2026-01-05 13:00:00',
        'duration_minutes' => 60,
        'status' => AppointmentStatus::CONFIRMED,
    ]);

    return compact('profile', 'appointment');
}

it('classifies only approved committed lifecycle events', function (): void {
    $action = new DetermineAppointmentNotificationEligibility;
    $appointment = Appointment::factory()->create();

    $created = AppointmentHistory::factory()->create([
        'appointment_id' => $appointment->id,
        'event_type' => AppointmentHistoryEventType::CREATED,
        'to_status' => AppointmentStatus::CONFIRMED,
    ]);
    $rescheduled = AppointmentHistory::factory()->create([
        'appointment_id' => $appointment->id,
        'event_type' => AppointmentHistoryEventType::RESCHEDULED,
        'from_status' => AppointmentStatus::CONFIRMED,
        'to_status' => AppointmentStatus::CONFIRMED,
    ]);
    $cancelled = AppointmentHistory::factory()->create([
        'appointment_id' => $appointment->id,
        'event_type' => AppointmentHistoryEventType::STATUS_CHANGED,
        'from_status' => AppointmentStatus::CONFIRMED,
        'to_status' => AppointmentStatus::CANCELLED,
    ]);
    $completed = AppointmentHistory::factory()->create([
        'appointment_id' => $appointment->id,
        'event_type' => AppointmentHistoryEventType::STATUS_CHANGED,
        'from_status' => AppointmentStatus::CONFIRMED,
        'to_status' => AppointmentStatus::COMPLETED,
    ]);
    $noShow = AppointmentHistory::factory()->create([
        'appointment_id' => $appointment->id,
        'event_type' => AppointmentHistoryEventType::STATUS_CHANGED,
        'from_status' => AppointmentStatus::CONFIRMED,
        'to_status' => AppointmentStatus::NO_SHOW,
    ]);

    expect($action->forHistory($created)->type)->toBe(NotificationType::APPOINTMENT_CONFIRMED)
        ->and($action->forHistory($rescheduled)->type)->toBe(NotificationType::APPOINTMENT_RESCHEDULED)
        ->and($action->forHistory($cancelled)->type)->toBe(NotificationType::APPOINTMENT_CANCELLED)
        ->and($action->forHistory($completed)->eligible)->toBeFalse()
        ->and($action->forHistory($noShow)->eligible)->toBeFalse()
        ->and($action->forHistory($created)->channel)->toBe(NotificationChannel::WHATSAPP);
});

it('derives a future 24-hour reminder occurrence in the business timezone', function (): void {
    $fixture = notificationEligibilityFixture('America/New_York');
    $now = CarbonImmutable::parse('2026-01-04 06:00:00', 'America/New_York');

    $result = (new DetermineAppointmentNotificationEligibility)->reminder($fixture['appointment'], $fixture['profile'], $now);

    expect($result->eligible)->toBeTrue()
        ->and($result->type)->toBe(NotificationType::APPOINTMENT_REMINDER)
        ->and($result->channel)->toBe(NotificationChannel::WHATSAPP)
        ->and($result->occursAt?->toIso8601String())->toBe('2026-01-04T12:00:00+00:00');
});

it('keeps the 24-hour reminder occurrence timezone-safe across a DST transition', function (): void {
    $fixture = notificationEligibilityFixture('America/New_York');
    $fixture['appointment']->update(['starts_at' => '2026-03-09 13:00:00', 'ends_at' => '2026-03-09 14:00:00']);
    $now = CarbonImmutable::parse('2026-03-08 08:00:00', 'America/New_York');

    $result = (new DetermineAppointmentNotificationEligibility)->reminder($fixture['appointment']->refresh(), $fixture['profile'], $now);

    expect($result->eligible)->toBeTrue()
        ->and($result->occursAt?->toIso8601String())->toBe('2026-03-08T13:00:00+00:00');
});

it('does not create a retroactive reminder for appointments inside the 24-hour window', function (): void {
    $fixture = notificationEligibilityFixture();
    $now = CarbonImmutable::parse('2026-01-05 08:00:00', 'UTC');

    $result = (new DetermineAppointmentNotificationEligibility)->reminder($fixture['appointment'], $fixture['profile'], $now);

    expect($result->eligible)->toBeFalse()->and($result->reason)->toBe('reminder_elapsed');
});

it('excludes past and terminal appointments from reminders', function (): void {
    $fixture = notificationEligibilityFixture();
    $action = new DetermineAppointmentNotificationEligibility;
    $now = CarbonImmutable::parse('2026-01-05 08:00:00', 'UTC');

    foreach ([AppointmentStatus::CANCELLED, AppointmentStatus::COMPLETED, AppointmentStatus::NO_SHOW] as $status) {
        $fixture['appointment']->update(['status' => $status]);
        expect($action->reminder($fixture['appointment']->refresh(), $fixture['profile'], $now)->eligible)->toBeFalse();
    }

    $fixture['appointment']->update(['status' => AppointmentStatus::CONFIRMED, 'starts_at' => '2026-01-04 12:00:00']);
    expect($action->reminder($fixture['appointment']->refresh(), $fixture['profile'], $now)->reason)->toBe('appointment_not_future');
});

it('does not mutate domain records while evaluating eligibility', function (): void {
    $fixture = notificationEligibilityFixture();
    $beforeAppointment = $fixture['appointment']->toArray();
    $beforeHistoryCount = AppointmentHistory::query()->where('appointment_id', $fixture['appointment']->id)->count();

    (new DetermineAppointmentNotificationEligibility)->reminder(
        $fixture['appointment'],
        $fixture['profile'],
        CarbonImmutable::parse('2026-01-04 08:00:00', 'UTC'),
    );

    expect($fixture['appointment']->refresh()->toArray())->toEqual($beforeAppointment)
        ->and(AppointmentHistory::query()->where('appointment_id', $fixture['appointment']->id)->count())->toBe($beforeHistoryCount);
});
