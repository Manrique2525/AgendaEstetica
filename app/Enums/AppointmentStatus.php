<?php

declare(strict_types=1);

namespace App\Enums;

enum AppointmentStatus: string
{
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case NO_SHOW = 'no_show';
}
