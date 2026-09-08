<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\BusinessProfile;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ReplaceBusinessHours
{
    public function execute(BusinessProfile $profile, array $intervals): void
    {
        $validated = $this->validateIntervals($intervals);

        DB::transaction(function () use ($profile, $validated): void {
            $lockedProfile = BusinessProfile::query()
                ->whereKey($profile->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedProfile === null) {
                throw (new ModelNotFoundException)->setModel(BusinessProfile::class, [$profile->getKey()]);
            }

            $lockedProfile->hours()->delete();

            $orders = [];

            foreach ($validated as $interval) {
                $weekday = $interval['weekday'];
                $orders[$weekday] = ($orders[$weekday] ?? 0) + 1;

                $lockedProfile->hours()->create([
                    ...$interval,
                    'interval_order' => $orders[$weekday],
                ]);
            }
        });
    }

    /**
     * @param  array<int, mixed>  $intervals
     * @return array<int, array{weekday: int, opens_at: string, closes_at: string}>
     */
    private function validateIntervals(array $intervals): array
    {
        $validated = [];

        foreach ($intervals as $index => $interval) {
            if (! is_array($interval)
                || ! array_key_exists('weekday', $interval)
                || ! array_key_exists('opens_at', $interval)
                || ! array_key_exists('closes_at', $interval)
                || ! is_int($interval['weekday'])
                || ! is_string($interval['opens_at'])
                || ! is_string($interval['closes_at'])) {
                throw new InvalidArgumentException("Business hour at index {$index} has an invalid structure.");
            }

            if ($interval['weekday'] < 1 || $interval['weekday'] > 7) {
                throw new InvalidArgumentException("Business hour at index {$index} has an invalid weekday.");
            }

            $opensAt = $this->timeToMinutes($interval['opens_at'], $index);
            $closesAt = $this->timeToMinutes($interval['closes_at'], $index);

            if ($opensAt >= $closesAt) {
                throw new InvalidArgumentException("Business hour at index {$index} must open before it closes.");
            }

            $validated[] = [
                'weekday' => $interval['weekday'],
                'opens_at' => $interval['opens_at'],
                'closes_at' => $interval['closes_at'],
            ];
        }

        foreach ($validated as $firstIndex => $first) {
            foreach (array_slice($validated, $firstIndex + 1) as $second) {
                if ($first['weekday'] !== $second['weekday']) {
                    continue;
                }

                $firstOpens = $this->timeToMinutes($first['opens_at'], $firstIndex);
                $firstCloses = $this->timeToMinutes($first['closes_at'], $firstIndex);
                $secondOpens = $this->timeToMinutes($second['opens_at'], $firstIndex);
                $secondCloses = $this->timeToMinutes($second['closes_at'], $firstIndex);

                if ($firstOpens < $secondCloses && $secondOpens < $firstCloses) {
                    throw new InvalidArgumentException('Business hours cannot overlap on the same weekday.');
                }
            }
        }

        return $validated;
    }

    private function timeToMinutes(string $time, int $index): int
    {
        $parsed = DateTimeImmutable::createFromFormat('!H:i', $time);

        if ($parsed === false || $parsed->format('H:i') !== $time) {
            throw new InvalidArgumentException("Business hour at index {$index} has an invalid time.");
        }

        return ((int) $parsed->format('H')) * 60 + (int) $parsed->format('i');
    }
}
