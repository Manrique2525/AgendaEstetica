<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BusinessHour;
use App\Models\BusinessProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Baseline business configuration.
 *
 * Ensures the singleton business profile and its opening hours exist so the
 * public booking flow and the administrative agenda can compute availability.
 * These values are operational configuration (timezone, capacity, reference
 * hours) taken from config/business.php, never invented marketing data.
 *
 * Safety guarantees:
 * - Idempotent: re-running does not duplicate the profile or the hours.
 * - Non-destructive: an existing profile configured by an administrator is
 *   never overwritten, and already-covered weekdays are left untouched.
 * - Safe for production.
 */
class BusinessProfileSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedProfile();
            $this->seedHours();
        });
    }

    private function seedProfile(): void
    {
        $profile = BusinessProfile::query()->firstOrNew(['singleton_key' => 1]);

        if ($profile->exists) {
            return;
        }

        $profile->fill([
            'name' => (string) config('business.name'),
            'phone' => (string) config('business.phone'),
            'timezone' => (string) config('business.timezone'),
            'max_simultaneous_clients' => (int) config('business.max_simultaneous_clients'),
        ]);
        $profile->save();
    }

    private function seedHours(): void
    {
        $profile = BusinessProfile::query()->where('singleton_key', 1)->first();

        if ($profile === null) {
            return;
        }

        $opensAt = (string) config('business.hours.opens_at');
        $closesAt = (string) config('business.hours.closes_at');
        /** @var array<int, int> $weekdays */
        $weekdays = config('business.hours.weekdays');

        $covered = $profile->hours()->pluck('weekday')->all();

        foreach ($weekdays as $weekday) {
            if (in_array($weekday, $covered, true)) {
                continue;
            }

            BusinessHour::query()->firstOrCreate(
                [
                    'business_profile_id' => $profile->id,
                    'weekday' => $weekday,
                ],
                [
                    'interval_order' => 1,
                    'opens_at' => $opensAt,
                    'closes_at' => $closesAt,
                ],
            );
        }
    }
}
