<?php

declare(strict_types=1);

namespace App\Support;

use DateTimeImmutable;
use InvalidArgumentException;

final class ProfessionalScheduleValidator
{
    /**
     * @param  array<int, mixed>  $intervals
     * @return array<int, array{weekday: int, starts_at: string, ends_at: string}>
     */
    public function validate(array $intervals): array
    {
        $validated = [];

        foreach ($intervals as $index => $interval) {
            if (! is_array($interval)
                || ! array_key_exists('weekday', $interval)
                || ! array_key_exists('starts_at', $interval)
                || ! array_key_exists('ends_at', $interval)
                || ! is_int($interval['weekday'])
                || ! is_string($interval['starts_at'])
                || ! is_string($interval['ends_at'])) {
                throw new InvalidArgumentException("Professional schedule at index {$index} has an invalid structure.");
            }

            if ($interval['weekday'] < 1 || $interval['weekday'] > 7) {
                throw new InvalidArgumentException("Professional schedule at index {$index} has an invalid weekday.");
            }

            $startsAt = $this->timeToMinutes($interval['starts_at'], $index);
            $endsAt = $this->timeToMinutes($interval['ends_at'], $index);

            if ($startsAt >= $endsAt) {
                throw new InvalidArgumentException("Professional schedule at index {$index} must start before it ends.");
            }

            $validated[] = [
                'weekday' => $interval['weekday'],
                'starts_at' => $interval['starts_at'],
                'ends_at' => $interval['ends_at'],
            ];
        }

        foreach ($validated as $firstIndex => $first) {
            foreach (array_slice($validated, $firstIndex + 1) as $second) {
                if ($first['weekday'] !== $second['weekday']) {
                    continue;
                }

                $firstStarts = $this->timeToMinutes($first['starts_at'], $firstIndex);
                $firstEnds = $this->timeToMinutes($first['ends_at'], $firstIndex);
                $secondStarts = $this->timeToMinutes($second['starts_at'], $firstIndex);
                $secondEnds = $this->timeToMinutes($second['ends_at'], $firstIndex);

                if ($firstStarts < $secondEnds && $secondStarts < $firstEnds) {
                    throw new InvalidArgumentException('Professional schedules cannot overlap on the same weekday.');
                }
            }
        }

        return $validated;
    }

    private function timeToMinutes(string $time, int $index): int
    {
        $parsed = DateTimeImmutable::createFromFormat('!H:i', $time);

        if ($parsed === false || $parsed->format('H:i') !== $time) {
            throw new InvalidArgumentException("Professional schedule at index {$index} has an invalid time.");
        }

        return ((int) $parsed->format('H')) * 60 + (int) $parsed->format('i');
    }
}
