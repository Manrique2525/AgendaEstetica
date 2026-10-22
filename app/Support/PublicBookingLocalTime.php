<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeZone;

final class PublicBookingLocalTime
{
    public function resolve(string $date, string $time, DateTimeZone $timezone): ?CarbonImmutable
    {
        $localText = "{$date} {$time}:00";
        $localAsUtc = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $localText, new DateTimeZone('UTC'));

        if ($localAsUtc === false) {
            return null;
        }

        $offsets = [];

        foreach ($timezone->getTransitions($localAsUtc->getTimestamp() - 172800, $localAsUtc->getTimestamp() + 172800) ?: [] as $transition) {
            $offsets[(int) $transition['offset']] = true;
        }

        $matches = [];

        foreach (array_keys($offsets) as $offset) {
            $candidate = (new DateTimeImmutable('@'.($localAsUtc->getTimestamp() - $offset)))->setTimezone($timezone);

            if ($candidate->format('Y-m-d H:i:s') === $localText) {
                $matches[$candidate->getTimestamp()] = $candidate;
            }
        }

        if (count($matches) !== 1) {
            return null;
        }

        return CarbonImmutable::instance(array_values($matches)[0]);
    }
}
