<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Professional;
use DateTimeInterface;
use InvalidArgumentException;

final class ProfessionalTimeOffOverlapValidator
{
    public function assertNoOverlap(
        Professional $professional,
        DateTimeInterface $startsAt,
        DateTimeInterface $endsAt,
    ): void {
        if ($startsAt >= $endsAt) {
            throw new InvalidArgumentException('Professional time off must start before it ends.');
        }

        $overlaps = $professional->timeOff()
            ->where('starts_at', '<', $endsAt->format('Y-m-d H:i:s'))
            ->where('ends_at', '>', $startsAt->format('Y-m-d H:i:s'))
            ->exists();

        if ($overlaps) {
            throw new InvalidArgumentException('Professional time off intervals cannot overlap.');
        }
    }
}
