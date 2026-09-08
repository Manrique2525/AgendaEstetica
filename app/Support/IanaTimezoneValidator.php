<?php

declare(strict_types=1);

namespace App\Support;

use DateTimeZone;
use InvalidArgumentException;

final class IanaTimezoneValidator
{
    public function isValid(string $timezone): bool
    {
        return in_array(
            $timezone,
            DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC),
            true,
        );
    }

    public function assertValid(string $timezone): void
    {
        if (! $this->isValid($timezone)) {
            throw new InvalidArgumentException('Timezone must be a recognized IANA identifier.');
        }
    }
}
