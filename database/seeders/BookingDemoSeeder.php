<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BusinessHour;
use App\Models\BusinessProfile;
use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Development/demo booking data only.
 *
 * This seeder prepares a complete, self-consistent booking scenario so the
 * public flow can be exercised locally: a singleton business profile with
 * opening hours, demo professionals, explicit professional↔service
 * assignments (including "Peinado"), and weekly schedules.
 *
 * Safety guarantees:
 * - It never runs on production (APP_ENV=production aborts before writing).
 * - It is idempotent: re-running does not duplicate professionals, schedules
 *   or relations, and it does not overwrite real data destructively.
 * - It never touches appointments or customers.
 * - All data is explicitly labelled as demo data.
 */
class BookingDemoSeeder extends Seeder
{
    private const DEMO_PREFIX = '[DEMO] ';

    public function run(): void
    {
        if (app()->environment('production') || config('app.env') === 'production') {
            throw new RuntimeException('BookingDemoSeeder must not run in production.');
        }

        $serviceIds = $this->resolveServiceIds();

        DB::transaction(function () use ($serviceIds): void {
            $this->seedBusinessProfile();
            $this->seedProfessionals($serviceIds);
        });
    }

    /**
     * Resolve the real service catalog by its stable slug. Never assume IDs.
     * "Peinado" is required for the demo flow to be verifiable.
     *
     * @return array<string, int>
     */
    private function resolveServiceIds(): array
    {
        $slugs = ['corte', 'peinado', 'tinte', 'unas', 'barberia'];

        $services = Service::query()
            ->whereIn('slug', $slugs)
            ->pluck('id', 'slug');

        if ($services->isEmpty()) {
            throw new RuntimeException(
                'BookingDemoSeeder requires the service catalog. Run ServiceCategorySeeder and ServiceCatalogSeeder first.',
            );
        }

        if (! $services->has('peinado')) {
            throw new RuntimeException('BookingDemoSeeder requires the "peinado" service to exist.');
        }

        return $services->all();
    }

    private function seedBusinessProfile(): void
    {
        $profile = BusinessProfile::query()->firstOrNew(['singleton_key' => 1]);

        // Never overwrite a profile an administrator already configured for real.
        if (! $profile->exists) {
            $profile->fill([
                'name' => 'Salón y Barbería Yaris (DEMO)',
                'phone' => '+520000000000',
                'timezone' => 'America/Mexico_City',
                'max_simultaneous_clients' => 2,
            ]);
            $profile->save();
        }

        // Only add missing opening hours; do not duplicate or delete existing ones.
        $defaults = [
            ['weekday' => 1, 'opens_at' => '10:00', 'closes_at' => '20:00'],
            ['weekday' => 2, 'opens_at' => '10:00', 'closes_at' => '20:00'],
            ['weekday' => 3, 'opens_at' => '10:00', 'closes_at' => '20:00'],
            ['weekday' => 4, 'opens_at' => '10:00', 'closes_at' => '20:00'],
            ['weekday' => 5, 'opens_at' => '10:00', 'closes_at' => '20:00'],
            ['weekday' => 6, 'opens_at' => '10:00', 'closes_at' => '20:00'],
            ['weekday' => 7, 'opens_at' => '10:00', 'closes_at' => '20:00'],
        ];

        $coveredWeekdays = $profile->hours()->pluck('weekday')->all();

        foreach ($defaults as $hour) {
            if (in_array($hour['weekday'], $coveredWeekdays, true)) {
                continue;
            }

            BusinessHour::query()->firstOrCreate(
                [
                    'business_profile_id' => $profile->id,
                    'weekday' => $hour['weekday'],
                    'opens_at' => $hour['opens_at'],
                    'closes_at' => $hour['closes_at'],
                ],
                ['interval_order' => 1],
            );
        }
    }

    /**
     * @param  array<string, int>  $serviceIds
     */
    private function seedProfessionals(array $serviceIds): void
    {
        $definitions = [
            [
                'name' => self::DEMO_PREFIX.'Ana (estílista)',
                'services' => array_values(array_filter([
                    $serviceIds['corte'] ?? null,
                    $serviceIds['peinado'] ?? null,
                    $serviceIds['tinte'] ?? null,
                ])),
            ],
            [
                'name' => self::DEMO_PREFIX.'Beatriz (uñas)',
                'services' => array_values(array_filter([
                    $serviceIds['unas'] ?? null,
                ])),
            ],
            [
                'name' => self::DEMO_PREFIX.'Carlos (barbería)',
                'services' => array_values(array_filter([
                    $serviceIds['corte'] ?? null,
                    $serviceIds['barberia'] ?? null,
                ])),
            ],
        ];

        foreach ($definitions as $definition) {
            $professional = Professional::query()->firstOrCreate(
                ['name' => $definition['name']],
                ['active' => true],
            );

            // Explicit, non-destructive assignment: add missing links only.
            if ($definition['services'] !== []) {
                $professional->services()->syncWithoutDetaching($definition['services']);
            }

            $this->seedSchedule($professional);
        }
    }

    private function seedSchedule(Professional $professional): void
    {
        if ($professional->schedules()->exists()) {
            return;
        }

        for ($weekday = 1; $weekday <= 7; $weekday++) {
            ProfessionalSchedule::query()->firstOrCreate([
                'professional_id' => $professional->id,
                'weekday' => $weekday,
                'starts_at' => '10:00',
                'ends_at' => '20:00',
            ]);
        }
    }
}
