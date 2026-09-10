<?php

use App\Enums\ServicePricingType;
use App\Models\BusinessProfile;
use App\Models\Professional;
use App\Models\Service;
use App\Models\ServiceCategory;

function publicBookingFixture(): array
{
    $profile = BusinessProfile::factory()->create(['timezone' => 'America/Mexico_City']);
    $category = ServiceCategory::factory()->create(['name' => 'Cuidado', 'active' => true]);
    $inactiveCategory = ServiceCategory::factory()->create(['name' => 'Oculta', 'active' => false]);
    $fixed = Service::factory()->create([
        'service_category_id' => $category->id,
        'name' => 'Corte fijo',
        'duration_minutes' => 60,
        'pricing_type' => ServicePricingType::FIXED,
        'price' => '350.00',
        'active' => true,
    ]);
    $starting = Service::factory()->create([
        'service_category_id' => $category->id,
        'name' => 'Color desde',
        'duration_minutes' => 90,
        'pricing_type' => ServicePricingType::STARTING_FROM,
        'price' => '500.00',
        'active' => true,
    ]);
    $variable = Service::factory()->create([
        'service_category_id' => $category->id,
        'name' => 'Diseño variable',
        'duration_minutes' => 45,
        'pricing_type' => ServicePricingType::VARIABLE,
        'price' => null,
        'active' => true,
    ]);
    $inactive = Service::factory()->create([
        'service_category_id' => $category->id,
        'name' => 'Servicio inactivo',
        'active' => false,
    ]);
    $hidden = Service::factory()->create([
        'service_category_id' => $inactiveCategory->id,
        'name' => 'Servicio oculto',
        'active' => true,
    ]);
    $compatible = Professional::factory()->create(['name' => 'Alex', 'active' => true]);
    $compatible->services()->attach([$fixed->id, $starting->id]);
    $incompatible = Professional::factory()->create(['name' => 'Bruno', 'active' => true]);
    $incompatible->services()->attach($variable);
    $inactiveProfessional = Professional::factory()->create(['name' => 'Caro', 'active' => false]);
    $inactiveProfessional->services()->attach($fixed);

    return compact('profile', 'category', 'fixed', 'starting', 'variable', 'inactive', 'hidden', 'compatible', 'incompatible', 'inactiveProfessional');
}

it('returns the public booking context without authentication or extra fields', function (): void {
    publicBookingFixture();

    $this->getJson('/api/v1/public/booking/context')
        ->assertOk()
        ->assertExactJson(['data' => ['timezone' => 'America/Mexico_City']]);
});

it('returns only active Services with minimal category and pricing projections', function (): void {
    $fixture = publicBookingFixture();
    $data = $this->getJson('/api/v1/public/booking/services')->assertOk()->json('data');
    $fixed = collect($data)->firstWhere('pricing_type', 'fixed');

    expect($fixed)->toMatchArray([
        'name' => 'Corte fijo',
        'pricing_type' => 'fixed',
        'price' => '350.00',
        'pricing_display' => '$350.00',
        'category' => ['id' => $fixture['category']->id, 'name' => 'Cuidado'],
    ])->not->toHaveKeys(['created_at', 'updated_at', 'active', 'description', 'phone']);

    expect(collect($data)->pluck('name')->all())
        ->toBe(['Color desde', 'Corte fijo', 'Diseño variable']);
});

it('serializes all authoritative Service pricing types without inventing amounts', function (): void {
    publicBookingFixture();

    $data = $this->getJson('/api/v1/public/booking/services')->assertOk()->json('data');

    expect(collect($data)->firstWhere('pricing_type', 'fixed'))->toHaveKey('pricing_display')
        ->and(collect($data)->firstWhere('pricing_type', 'starting_from'))
        ->toMatchArray(['price' => '500.00', 'pricing_display' => 'Desde $500.00'])
        ->and(collect($data)->firstWhere('pricing_type', 'variable'))
        ->toMatchArray(['price' => null, 'pricing_display' => 'Precio variable']);
});

it('filters inactive Services and inactive categories from the public catalog', function (): void {
    $fixture = publicBookingFixture();

    $names = collect($this->getJson('/api/v1/public/booking/services')->assertOk()->json('data'))
        ->pluck('name')->all();

    expect($names)
        ->toContain('Corte fijo', 'Color desde', 'Diseño variable')
        ->not->toContain($fixture['inactive']->name, $fixture['hidden']->name);
});

it('returns active compatible Professionals for a valid Service only', function (): void {
    $fixture = publicBookingFixture();

    $response = $this->getJson("/api/v1/public/booking/professionals?service_id={$fixture['fixed']->id}")
        ->assertOk()
        ->assertJsonPath('data.0.id', $fixture['compatible']->id)
        ->assertJsonPath('data.0.name', 'Alex')
        ->assertJsonMissingPath('data.0.created_at')
        ->assertJsonMissingPath('data.0.active')
        ->assertJsonMissingPath('data.0.services')
        ->assertJsonMissingPath('data.0.schedules');

    expect($response->json('data'))->toHaveCount(1);
});

it('does not return Professionals for inactive or unknown Services', function (): void {
    $fixture = publicBookingFixture();

    $this->getJson("/api/v1/public/booking/professionals?service_id={$fixture['inactive']->id}")
        ->assertOk()->assertExactJson(['data' => []]);

    $this->getJson('/api/v1/public/booking/professionals?service_id=999999')
        ->assertOk()->assertExactJson(['data' => []]);
});

it('requires a positive integer service_id for Professional lookup', function (string $query): void {
    publicBookingFixture();

    $this->getJson("/api/v1/public/booking/professionals?service_id={$query}")
        ->assertStatus(422)
        ->assertJsonValidationErrors('service_id');
})->with(['missing' => '', 'invalid' => 'abc', 'zero' => '0']);

it('returns JSON 404 for an unknown public booking route', function (): void {
    $this->get('/api/v1/public/booking/not-real')
        ->assertNotFound()
        ->assertJsonStructure(['message'])
        ->assertHeader('content-type', 'application/json');
});

it('rate-limits public catalog reads by IP with a safe JSON 429', function (): void {
    publicBookingFixture();
    $request = fn () => $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.10'])
        ->getJson('/api/v1/public/booking/context');

    foreach (range(1, 120) as $attempt) {
        $request()->assertOk();
    }

    $request()
        ->assertTooManyRequests()
        ->assertJsonStructure(['message'])
        ->assertJsonMissingPath('exception');
});

it('rate-limits public Professional reads independently by IP', function (): void {
    $fixture = publicBookingFixture();
    $request = fn () => $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.11'])
        ->getJson("/api/v1/public/booking/professionals?service_id={$fixture['fixed']->id}");

    foreach (range(1, 60) as $attempt) {
        $request()->assertOk();
    }

    $request()->assertTooManyRequests()->assertJsonStructure(['message']);
});
