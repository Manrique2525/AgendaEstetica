<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\BusinessHour;
use App\Models\BusinessProfile;
use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use App\Models\ProfessionalTimeOff;
use App\Models\Service;
use App\Models\ServiceCategory;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeZone;

final class CheckAppointmentAvailability
{
    public function execute(
        Service $service,
        Professional $professional,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
        ?int $excludeAppointmentId = null,
        ?int $expectedDurationMinutes = null,
    ): bool {
        if ($startsAt->getTimestamp() >= $endsAt->getTimestamp()) {
            return false;
        }

        $durationMinutes = $expectedDurationMinutes ?? $service->duration_minutes;

        if (($endsAt->getTimestamp() - $startsAt->getTimestamp()) !== ($durationMinutes * 60)) {
            return false;
        }

        $profile = BusinessProfile::query()->first();
        $category = ServiceCategory::query()->find($service->service_category_id);

        if ($profile === null
            || $category === null
            || ! $category->active
            || ! $service->active
            || ! $professional->active
            || ! $professional->services()->whereKey($service->getKey())->exists()) {
            return false;
        }

        try {
            $timezone = new DateTimeZone($profile->timezone);
        } catch (\Exception) {
            return false;
        }

        if (! $this->coversRecurringIntervals(
            $profile->hours()->get()->map(static function ($hour): array {
                /** @var BusinessHour $hour */
                return [
                    'weekday' => $hour->weekday,
                    'starts_at' => $hour->opens_at,
                    'ends_at' => $hour->closes_at,
                ];
            })->all(),
            $startsAt,
            $endsAt,
            $timezone,
            'starts_at',
            'ends_at',
        )) {
            return false;
        }

        if (! $this->coversRecurringIntervals(
            $professional->schedules()->get()->map(static function ($schedule): array {
                /** @var ProfessionalSchedule $schedule */
                return [
                    'weekday' => $schedule->weekday,
                    'starts_at' => $schedule->starts_at,
                    'ends_at' => $schedule->ends_at,
                ];
            })->all(),
            $startsAt,
            $endsAt,
            $timezone,
            'starts_at',
            'ends_at',
        )) {
            return false;
        }

        if (ProfessionalTimeOff::query()
            ->where('professional_id', $professional->getKey())
            ->where('starts_at', '<', $endsAt->toDateTimeString())
            ->where('ends_at', '>', $startsAt->toDateTimeString())
            ->exists()) {
            return false;
        }

        if ($professional->appointments()
            ->where('status', AppointmentStatus::CONFIRMED)
            ->when($excludeAppointmentId !== null, fn ($query) => $query->where('appointments.id', '<>', $excludeAppointmentId))
            ->where('starts_at', '<', $endsAt->toDateTimeString())
            ->where('ends_at', '>', $startsAt->toDateTimeString())
            ->exists()) {
            return false;
        }

        return $this->capacityAllows($profile->max_simultaneous_clients, $startsAt, $endsAt, $excludeAppointmentId);
    }

    /**
     * @param  array<int, array{weekday: int, starts_at: string, ends_at: string}>  $rows
     */
    private function coversRecurringIntervals(
        array $rows,
        CarbonImmutable $candidateStart,
        CarbonImmutable $candidateEnd,
        DateTimeZone $timezone,
        string $startsField,
        string $endsField,
    ): bool {
        $localStart = DateTimeImmutable::createFromInterface($candidateStart)->setTimezone($timezone);
        $localEnd = DateTimeImmutable::createFromInterface($candidateEnd)->setTimezone($timezone);
        $date = $localStart->setTime(0, 0, 0);
        $lastDate = $localEnd->setTime(0, 0, 0);
        $intervals = [];

        while ($date <= $lastDate) {
            $weekday = (int) $date->format('N');

            foreach (array_filter($rows, static fn (array $row): bool => $row['weekday'] === $weekday) as $row) {
                $start = $this->resolveLocalBoundary($date->format('Y-m-d'), $row[$startsField], $timezone);
                $end = $this->resolveLocalBoundary($date->format('Y-m-d'), $row[$endsField], $timezone);

                if ($start === null || $end === null || $start >= $end) {
                    return false;
                }

                $intervals[] = [$start->getTimestamp(), $end->getTimestamp()];
            }

            $date = $date->modify('+1 day');
        }

        usort($intervals, static fn (array $left, array $right): int => $left[0] <=> $right[0]);
        $merged = [];

        foreach ($intervals as [$start, $end]) {
            $last = array_key_last($merged);

            if ($last !== null && $start <= $merged[$last][1]) {
                $merged[$last][1] = max($merged[$last][1], $end);
            } else {
                $merged[] = [$start, $end];
            }
        }

        $candidateStartTimestamp = $candidateStart->getTimestamp();
        $candidateEndTimestamp = $candidateEnd->getTimestamp();

        foreach ($merged as [$start, $end]) {
            if ($start <= $candidateStartTimestamp && $end >= $candidateEndTimestamp) {
                return true;
            }
        }

        return false;
    }

    private function resolveLocalBoundary(string $date, string $time, DateTimeZone $timezone): ?DateTimeImmutable
    {
        $time = strlen($time) === 5 ? $time.':00' : substr($time, 0, 8);
        $localText = "{$date} {$time}";
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

        return count($matches) === 1 ? array_values($matches)[0] : null;
    }

    private function capacityAllows(
        int $capacity,
        CarbonImmutable $candidateStart,
        CarbonImmutable $candidateEnd,
        ?int $excludeAppointmentId = null,
    ): bool {
        /** @var array<int, array{0: int, 1: int}> $events */
        $events = [
            [$candidateStart->getTimestamp(), 1],
            [$candidateEnd->getTimestamp(), -1],
        ];

        foreach (Appointment::query()
            ->where('status', AppointmentStatus::CONFIRMED)
            ->when($excludeAppointmentId !== null, fn ($query) => $query->where('appointments.id', '<>', $excludeAppointmentId))
            ->when($excludeAppointmentId !== null, fn ($query) => $query->where('appointments.id', '<>', $excludeAppointmentId))
            ->where('starts_at', '<', $candidateEnd->toDateTimeString())
            ->where('ends_at', '>', $candidateStart->toDateTimeString())
            ->get(['starts_at', 'ends_at']) as $appointment) {
            $start = max($candidateStart->getTimestamp(), CarbonImmutable::parse((string) $appointment->starts_at, 'UTC')->getTimestamp());
            $end = min($candidateEnd->getTimestamp(), CarbonImmutable::parse((string) $appointment->ends_at, 'UTC')->getTimestamp());

            $events[] = [$start, 1];
            $events[] = [$end, -1];
        }

        usort($events, static fn (array $left, array $right): int => $left[0] === $right[0]
            ? $left[1] <=> $right[1]
            : $left[0] <=> $right[0]);

        $current = 0;

        foreach ($events as [, $delta]) {
            $current += $delta;

            if ($current > $capacity) {
                return false;
            }
        }

        return true;
    }
}
