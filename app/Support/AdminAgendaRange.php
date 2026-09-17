<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\BusinessProfile;
use Carbon\CarbonImmutable;
use DateTimeZone;
use InvalidArgumentException;

final class AdminAgendaRange
{
    /**
     * @return array{from: CarbonImmutable, to: CarbonImmutable}
     */
    public function resolve(string $from, string $to): array
    {
        $profile = BusinessProfile::query()->where('singleton_key', 1)->first();

        if ($profile === null) {
            throw new InvalidArgumentException('Business profile is not configured.');
        }

        try {
            $timezone = new DateTimeZone($profile->timezone);
        } catch (\Exception) {
            throw new InvalidArgumentException('Business timezone is invalid.');
        }

        $fromLocal = $this->parseDate($from, $timezone);
        $toLocal = $this->parseDate($to, $timezone);
        $days = $fromLocal->diffInDays($toLocal);

        if ($days < 1 || $days > 31) {
            throw new InvalidArgumentException('Agenda range must contain between one and 31 calendar days.');
        }

        return [
            'from' => $fromLocal->startOfDay()->setTimezone('UTC'),
            'to' => $toLocal->startOfDay()->setTimezone('UTC'),
        ];
    }

    private function parseDate(string $value, DateTimeZone $timezone): CarbonImmutable
    {
        $date = CarbonImmutable::createFromFormat('!Y-m-d', $value, $timezone);

        if (! $date instanceof CarbonImmutable || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException('Agenda dates must use the YYYY-MM-DD format.');
        }

        return $date;
    }
}
