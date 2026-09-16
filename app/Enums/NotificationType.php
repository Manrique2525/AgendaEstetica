<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationType: string
{
    case APPOINTMENT_CONFIRMED = 'appointment_confirmed';
    case APPOINTMENT_RESCHEDULED = 'appointment_rescheduled';
    case APPOINTMENT_CANCELLED = 'appointment_cancelled';
    case APPOINTMENT_REMINDER = 'appointment_reminder';
}
