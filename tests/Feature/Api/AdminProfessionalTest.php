<?php

use App\Models\Appointment;
use App\Models\BusinessHour;
use App\Models\BusinessProfile;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\ServiceCatalogSeeder;
use Database\Seeders\ServiceCategorySeeder;

beforeEach(function (): void {
    $this->admin = User::factory()->admin()->create();
    $this->seed(ServiceCategorySeeder::class);
    $this->seed(ServiceCatalogSeeder::class);
});

it('requires admin authentication for professional endpoints', function (): void {
    $this->getJson('/api/v1/admin/professionals')->assertUnauthorized();

    $regular = User::factory()->create();
    $this->actingAs($regular)->getJson('/api/v1/admin/professionals')->assertForbidden();
});

it('creates and lists professionals with service assignment', function (): void {
    $corte = Service::query()->where('slug', 'corte')->firstOrFail();
    $peinado = Service::query()->where('slug', 'peinado')->firstOrFail();

    $this->actingAs($this->admin)
        ->postJson('/api/v1/admin/professionals', [
            'name' => 'Diana',
            'service_ids' => [$corte->id, $peinado->id],
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Diana')
        ->assertJsonPath('data.active', true)
        ->assertJsonCount(2, 'data.services');

    $this->actingAs($this->admin)
        ->getJson('/api/v1/admin/professionals')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Diana');
});

it('filters professionals by service', function (): void {
    $corte = Service::query()->where('slug', 'corte')->firstOrFail();
    $unas = Service::query()->where('slug', 'unas')->firstOrFail();

    $a = Professional::factory()->create(['name' => 'A']);
    $a->services()->attach($corte->id);
    $b = Professional::factory()->create(['name' => 'B']);
    $b->services()->attach($unas->id);

    $this->actingAs($this->admin)
        ->getJson('/api/v1/admin/professionals?service_id='.$corte->id)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'A');
});

it('updates a professional and replaces service assignment', function (): void {
    $corte = Service::query()->where('slug', 'corte')->firstOrFail();
    $unas = Service::query()->where('slug', 'unas')->firstOrFail();

    $professional = Professional::factory()->create(['name' => 'Eva']);
    $professional->services()->attach($corte->id);

    $this->actingAs($this->admin)
        ->putJson("/api/v1/admin/professionals/{$professional->id}", [
            'name' => 'Eva María',
            'service_ids' => [$unas->id],
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Eva María')
        ->assertJsonCount(1, 'data.services')
        ->assertJsonPath('data.services.0.id', $unas->id);
});

it('toggles professional status', function (): void {
    $professional = Professional::factory()->create(['active' => true]);

    $this->actingAs($this->admin)
        ->patchJson("/api/v1/admin/professionals/{$professional->id}/status", ['active' => false])
        ->assertOk()
        ->assertJsonPath('data.active', false);

    expect($professional->fresh()->active)->toBeFalse();
});

it('blocks deletion when the professional has appointments', function (): void {
    $professional = Professional::factory()->create();
    Appointment::factory()->create(['professional_id' => $professional->id]);

    $this->actingAs($this->admin)
        ->deleteJson("/api/v1/admin/professionals/{$professional->id}")
        ->assertStatus(409)
        ->assertJsonPath('code', 'professional_has_appointments');
});

it('replaces professional weekly schedule', function (): void {
    $professional = Professional::factory()->create();

    $this->actingAs($this->admin)
        ->putJson("/api/v1/admin/professionals/{$professional->id}/schedules", [
            'intervals' => [
                ['weekday' => 1, 'starts_at' => '09:00', 'ends_at' => '14:00'],
                ['weekday' => 2, 'starts_at' => '09:00', 'ends_at' => '14:00'],
            ],
        ])
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $this->actingAs($this->admin)
        ->getJson("/api/v1/admin/professionals/{$professional->id}/schedules")
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('updates the singleton business profile and its hours', function (): void {
    $this->actingAs($this->admin)
        ->putJson('/api/v1/admin/business/profile', [
            'name' => 'Salón DEMO',
            'phone' => '+520000000000',
            'timezone' => 'America/Mexico_City',
            'max_simultaneous_clients' => 3,
            'hours' => [
                ['weekday' => 1, 'opens_at' => '10:00', 'closes_at' => '20:00'],
            ],
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Salón DEMO')
        ->assertJsonPath('data.max_simultaneous_clients', 3)
        ->assertJsonCount(1, 'data.hours');

    expect(BusinessProfile::query()->count())->toBe(1)
        ->and(BusinessHour::query()->count())->toBe(1);
});

it('validates the business profile timezone', function (): void {
    $this->actingAs($this->admin)
        ->putJson('/api/v1/admin/business/profile', [
            'name' => 'X',
            'phone' => '+520000000000',
            'timezone' => 'Not/AZone',
            'max_simultaneous_clients' => 2,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('timezone');
});
