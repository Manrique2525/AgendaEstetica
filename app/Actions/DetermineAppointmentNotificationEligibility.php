<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\AppointmentHistoryEventType;
use App\Enums\AppointmentStatus;
use App\Enums\NotificationType;
use App\Models\Appointment;
use App\Models\AppointmentHistory;
use App\Models\BusinessProfile;
use App\Support\NotificationEligibility;
use Carbon\CarbonImmutable;

final class DetermineAppointmentNotificationEligibility
{
    public function forHistory(AppointmentHistory $history): NotificationEligibility
    {
        /** @var AppointmentHistoryEventType $eventType */
        $eventType = $history->getAttribute('event_type');
        /** @var AppointmentStatus|null $toStatus */
        $toStatus = $history->getAttribute('to_status');

        return match ($eventType) {
            AppointmentHistoryEventType::CREATED => $toStatus === AppointmentStatus::CONFIRMED
                ? NotificationEligibility::eligible(NotificationType::APPOINTMENT_CONFIRMED)
                : NotificationEligibility::ineligible('created_not_confirmed'),
            AppointmentHistoryEventType::RESCHEDULED => NotificationEligibility::eligible(NotificationType::APPOINTMENT_RESCHEDULED),
            AppointmentHistoryEventType::STATUS_CHANGED => $toStatus === AppointmentStatus::CANCELLED
                ? NotificationEligibility::eligible(NotificationType::APPOINTMENT_CANCELLED)
                : NotificationEligibility::ineligible('status_not_notifiable'),
        };
    }

    public function reminder(
        Appointment $appointment,
        BusinessProfile $profile,
        CarbonImmutable $now,
    ): NotificationEligibility {
        /** @var AppointmentStatus $status */
        $status = $appointment->getAttribute('status');

        if ($status !== AppointmentStatus::CONFIRMED) {
            return NotificationEligibility::ineligible('appointment_not_confirmed');
        }

        /** @var \DateTimeInterface $startsAtValue */
        $startsAtValue = $appointment->getAttribute('starts_at');
        $startsAt = CarbonImmutable::instance($startsAtValue)->setTimezone($profile->timezone);
        $nowInBusinessTimezone = $now->setTimezone($profile->timezone);

        if ($startsAt->lessThanOrEqualTo($nowInBusinessTimezone)) {
            return NotificationEligibility::ineligible('appointment_not_future');
        }

        $reminderAt = $startsAt->subHours(24);
        if ($reminderAt->lessThanOrEqualTo($nowInBusinessTimezone)) {
            return NotificationEligibility::ineligible('reminder_elapsed');
        }

        return NotificationEligibility::eligible(NotificationType::APPOINTMENT_REMINDER, $reminderAt->utc());
    }
}
