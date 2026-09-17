<?php

declare(strict_types=1);

namespace App\Enums;

enum AppointmentHistoryEventType: string
{
    case CREATED = 'created';
    case STATUS_CHANGED = 'status_changed';
    case RESCHEDULED = 'rescheduled';
}
