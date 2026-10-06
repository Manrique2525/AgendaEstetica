<?php

use App\Enums\AppointmentStatus;

it('defines only the approved Appointment statuses', function (): void {
    expect(array_map(
        static fn (AppointmentStatus $status): string => $status->value,
        AppointmentStatus::cases(),
    ))->toBe(['confirmed', 'cancelled', 'completed', 'no_show']);
});
