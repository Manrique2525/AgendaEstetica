<?php

use App\Models\BusinessHour;
use App\Models\BusinessProfile;
use Database\Seeders\BusinessProfileSeeder;

it('creates the singleton business profile with the configured baseline', function (): void {
    $this->seed(BusinessProfileSeeder::class);

    expect(BusinessProfile::query()->count())->toBe(1)
        ->and(BusinessHour::query()->count())->toBe(7);

    $profile = BusinessProfile::query()->firstOrFail();

    expect($profile->singleton_key)->toBe(1)
        ->and($profile->timezone)->toBe(config('business.timezone'))
        ->and($profile->max_simultaneous_clients)->toBe((int) config('business.max_simultaneous_clients'));

    expect($profile->hours->pluck('weekday')->sort()->values()->all())->toBe([1, 2, 3, 4, 5, 6, 7]);
});

it('is idempotent and does not duplicate the profile or its hours', function (): void {
    $this->seed(BusinessProfileSeeder::class);
    $this->seed(BusinessProfileSeeder::class);

    expect(BusinessProfile::query()->count())->toBe(1)
        ->and(BusinessHour::query()->count())->toBe(7);
});

it('does not overwrite a profile already configured by an administrator', function (): void {
    BusinessProfile::factory()->create([
        'singleton_key' => 1,
        'name' => 'Configurado por admin',
        'timezone' => 'America/Merida',
        'max_simultaneous_clients' => 3,
    ]);

    $this->seed(BusinessProfileSeeder::class);

    $profile = BusinessProfile::query()->firstOrFail();

    expect($profile->name)->toBe('Configurado por admin')
        ->and($profile->timezone)->toBe('America/Merida')
        ->and($profile->max_simultaneous_clients)->toBe(3);
});
