<?php

use App\Models\BusinessHour;
use App\Models\BusinessProfile;
use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use App\Models\Service;
use Database\Seeders\BookingDemoSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Database\Seeders\ServiceCategorySeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->seed(ServiceCategorySeeder::class);
    $this->seed(ServiceCatalogSeeder::class);
});

it('creates a complete demo booking scenario', function (): void {
    $this->seed(BookingDemoSeeder::class);

    expect(BusinessProfile::query()->count())->toBe(1)
        ->and(BusinessHour::query()->count())->toBe(7)
        ->and(Professional::query()->count())->toBe(3)
        ->and(ProfessionalSchedule::query()->count())->toBe(21);

    $profile = BusinessProfile::query()->firstOrFail();
    expect($profile->singleton_key)->toBe(1)
        ->and($profile->name)->toContain('DEMO')
        ->and($profile->timezone)->toBe('America/Mexico_City');
});

it('assigns the peinado service to at least one demo professional', function (): void {
    $this->seed(BookingDemoSeeder::class);

    $peinado = Service::query()->where('slug', 'peinado')->firstOrFail();

    expect($peinado->professionals()->where('active', true)->count())->toBeGreaterThanOrEqual(1);
});

it('is idempotent and does not duplicate records when run twice', function (): void {
    $this->seed(BookingDemoSeeder::class);
    $this->seed(BookingDemoSeeder::class);

    expect(BusinessProfile::query()->count())->toBe(1)
        ->and(BusinessHour::query()->count())->toBe(7)
        ->and(Professional::query()->count())->toBe(3)
        ->and(DB::table('professional_service')->count())->toBe(6)
        ->and(ProfessionalSchedule::query()->count())->toBe(21);
});

it('does not remove existing appointments or detach admin assignments', function (): void {
    $this->seed(BookingDemoSeeder::class);

    $professional = Professional::query()->firstOrFail();
    $extra = Service::query()->where('slug', 'corte')->firstOrFail();

    $this->seed(BookingDemoSeeder::class);

    $professional->refresh();

    expect($professional->services()->whereKey($extra->id)->exists())->toBeTrue();
});

it('does not overwrite a real business profile already configured by an administrator', function (): void {
    $real = BusinessProfile::factory()->create([
        'singleton_key' => 1,
        'name' => 'Salón Real',
        'phone' => '+529931112233',
        'timezone' => 'America/Tijuana',
        'max_simultaneous_clients' => 5,
    ]);
    $real->hours()->create(['weekday' => 1, 'interval_order' => 1, 'opens_at' => '08:00', 'closes_at' => '12:00']);

    $this->seed(BookingDemoSeeder::class);

    $real->refresh();

    expect($real->name)->toBe('Salón Real')
        ->and($real->phone)->toBe('+529931112233')
        ->and($real->timezone)->toBe('America/Tijuana')
        ->and($real->max_simultaneous_clients)->toBe(5)
        ->and($real->hours()->where('weekday', 1)->value('opens_at'))->toBe('08:00:00');
});

it('aborts on production environment', function (): void {
    config(['app.env' => 'production']);

    expect(fn () => $this->seed(BookingDemoSeeder::class))
        ->toThrow(RuntimeException::class);
});
