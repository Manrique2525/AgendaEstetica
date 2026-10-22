<?php

use App\Models\Appointment;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Database\Seeders\BookingDemoSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Database\Seeders\ServiceCategorySeeder;

beforeEach(function (): void {
    Carbon::setTestNow(CarbonImmutable::parse('2026-01-05 08:00:00', 'UTC'));

    $this->seed(ServiceCategorySeeder::class);
    $this->seed(ServiceCatalogSeeder::class);
    $this->seed(BookingDemoSeeder::class);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('exposes the demo business context publicly', function (): void {
    $this->getJson('/api/v1/public/booking/context')
        ->assertOk()
        ->assertJsonPath('data.timezone', 'America/Mexico_City');
});

it('returns the demo professionals assigned to the peinado service', function (): void {
    $peinado = Service::query()->where('slug', 'peinado')->firstOrFail();

    $response = $this->getJson('/api/v1/public/booking/professionals?service_id='.$peinado->id)
        ->assertOk();

    $names = collect($response->json('data'))->pluck('name');

    expect($names)->not->toBeEmpty()
        ->and($names->every(fn (string $name): bool => str_contains($name, 'DEMO')))->toBeTrue();
});

it('returns bookable slots for a demo professional and confirms a booking end to end', function (): void {
    $peinado = Service::query()->where('slug', 'peinado')->firstOrFail();
    $professional = $peinado->professionals()->where('active', true)->firstOrFail();

    $availability = $this->getJson(
        '/api/v1/public/booking/availability?service_id='.$peinado->id
        .'&professional_id='.$professional->id
        .'&date=2026-01-05',
    )->assertOk();

    $slot = $availability->json('data.slots.0');
    expect($slot)->not->toBeNull();

    $this->withHeaders(['Idempotency-Key' => '22222222-2222-4222-8222-222222222222'])
        ->postJson('/api/v1/public/booking/appointments', [
            'service_id' => $peinado->id,
            'professional_id' => $professional->id,
            'starts_at' => $slot['starts_at'],
            'name' => 'Cliente Demo',
            'phone' => '993 100 2000',
        ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.service.name', $peinado->name);

    expect(Appointment::query()->count())->toBe(1);
});

it('excludes demo professionals without the requested service', function (): void {
    $peinado = Service::query()->where('slug', 'peinado')->firstOrFail();
    $unas = Service::query()->where('slug', 'unas')->firstOrFail();

    $forPeinado = collect(
        $this->getJson('/api/v1/public/booking/professionals?service_id='.$peinado->id)->json('data'),
    )->pluck('id')->all();

    $forUnas = collect(
        $this->getJson('/api/v1/public/booking/professionals?service_id='.$unas->id)->json('data'),
    )->pluck('id')->all();

    expect($forPeinado)->not->toBeEmpty()
        ->and($forUnas)->not->toBeEmpty()
        ->and(array_intersect($forPeinado, $forUnas))->toBeEmpty();
});

it('excludes inactive demo professionals from the public list', function (): void {
    $peinado = Service::query()->where('slug', 'peinado')->firstOrFail();
    Professional::query()->whereHas('services', fn ($q) => $q->whereKey($peinado->id))->update(['active' => false]);

    $this->getJson('/api/v1/public/booking/professionals?service_id='.$peinado->id)
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('shows a publicly booked appointment in the administrative agenda for its business date', function (): void {
    $peinado = Service::query()->where('slug', 'peinado')->firstOrFail();
    $professional = $peinado->professionals()->where('active', true)->firstOrFail();

    $slot = $this->getJson(
        '/api/v1/public/booking/availability?service_id='.$peinado->id
        .'&professional_id='.$professional->id
        .'&date=2026-01-05',
    )->assertOk()->json('data.slots.0');

    $this->withHeaders(['Idempotency-Key' => '33333333-3333-4333-8333-333333333333'])
        ->postJson('/api/v1/public/booking/appointments', [
            'service_id' => $peinado->id,
            'professional_id' => $professional->id,
            'starts_at' => $slot['starts_at'],
            'name' => 'Cliente Agenda',
            'phone' => '993 100 3000',
        ])->assertCreated();

    $appointment = Appointment::query()->firstOrFail();

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->getJson('/api/v1/admin/agenda/appointments?from=2026-01-05&to=2026-01-06')
        ->assertOk()
        ->assertJsonPath('data.0.id', $appointment->id);
});
