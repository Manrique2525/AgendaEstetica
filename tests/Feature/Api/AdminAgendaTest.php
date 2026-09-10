<?php

use App\Actions\CreateAppointment;
use App\Enums\AppointmentHistoryEventType;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Support\AdminAgendaRange;
use Carbon\CarbonImmutable;

function adminAgendaFixture(string $timezone = 'UTC'): array
{
    $profile = BusinessProfile::factory()->create(['timezone' => $timezone]);
    $profile->hours()->create(['weekday' => 1, 'interval_order' => 1, 'opens_at' => '09:00', 'closes_at' => '18:00']);
    $category = ServiceCategory::factory()->create(['active' => true]);
    $service = Service::factory()->create(['service_category_id' => $category->id, 'active' => true, 'duration_minutes' => 60]);
    $professional = Professional::factory()->create(['active' => true]);
    $professional->services()->attach($service);
    $professional->schedules()->create(['weekday' => 1, 'starts_at' => '09:00', 'ends_at' => '18:00']);

    return [
        'profile' => $profile,
        'category' => $category,
        'service' => $service,
        'professional' => $professional,
        'customer' => Customer::factory()->create(),
        'admin' => User::factory()->create(),
    ];
}

function adminAgendaAppointment(array $fixture, string $startsAt, string $endsAt): Appointment
{
    return Appointment::factory()->create([
        'customer_id' => $fixture['customer']->id,
        'service_id' => $fixture['service']->id,
        'professional_id' => $fixture['professional']->id,
        'starts_at' => $startsAt,
        'ends_at' => $endsAt,
        'duration_minutes' => 60,
        'status' => AppointmentStatus::CONFIRMED,
    ]);
}

it('requires the authenticated admin session for read endpoints', function (string $endpoint): void {
    expect($this->get($endpoint)->status())->toBe(401);
})->with([
    '/api/v1/admin/agenda/appointments?from=2026-01-05&to=2026-01-06',
    '/api/v1/admin/agenda/context',
    '/api/v1/admin/agenda/customers?q=ana',
    '/api/v1/admin/agenda/services',
    '/api/v1/admin/agenda/professionals',
]);

it('returns the authoritative business timezone with no extra context fields', function (): void {
    $fixture = adminAgendaFixture('America/New_York');

    $this->actingAs($fixture['admin'])
        ->getJson('/api/v1/admin/agenda/context')
        ->assertOk()
        ->assertExactJson(['data' => ['timezone' => 'America/New_York']]);
});

it('validates bounded business-local agenda ranges', function (): void {
    $fixture = adminAgendaFixture();
    $this->actingAs($fixture['admin']);

    $this->getJson('/api/v1/admin/agenda/appointments?from=2026-01-05&to=2026-02-06')
        ->assertStatus(422)->assertJsonValidationErrors('from');

    $this->getJson('/api/v1/admin/agenda/appointments?from=2026-01-06&to=2026-01-05')
        ->assertStatus(422)->assertJsonValidationErrors('from');
});

it('lists intersecting appointments with filters and minimal card projection', function (): void {
    $fixture = adminAgendaFixture();
    $appointment = adminAgendaAppointment($fixture, '2026-01-05 23:30:00', '2026-01-06 00:30:00');
    Appointment::factory()->create([
        'customer_id' => Customer::factory()->create()->id,
        'service_id' => $fixture['service']->id,
        'professional_id' => $fixture['professional']->id,
        'starts_at' => '2026-01-06 01:00:00',
        'ends_at' => '2026-01-06 02:00:00',
        'duration_minutes' => 60,
        'status' => AppointmentStatus::CANCELLED,
    ]);

    $this->actingAs($fixture['admin'])
        ->getJson('/api/v1/admin/agenda/appointments?from=2026-01-06&to=2026-01-07&status=confirmed')
        ->assertOk()
        ->assertJsonPath('data.0.id', $appointment->id)
        ->assertJsonPath('data.0.status', 'confirmed')
        ->assertJsonMissingPath('data.0.customer.phone')
        ->assertJsonMissingPath('data.0.customer.phone_normalized');
});

it('resolves business-local date ranges against UTC appointments', function (): void {
    $fixture = adminAgendaFixture('America/New_York');
    $included = adminAgendaAppointment($fixture, '2026-01-06 04:30:00', '2026-01-06 05:30:00');

    $this->actingAs($fixture['admin'])
        ->getJson('/api/v1/admin/agenda/appointments?from=2026-01-05&to=2026-01-06')
        ->assertOk()->assertJsonPath('data.0.id', $included->id);
});

it('resolves a spring-forward business day as a 23-hour UTC range and filters appointments', function (): void {
    $fixture = adminAgendaFixture('America/New_York');
    $included = adminAgendaAppointment($fixture, '2026-03-08 04:30:00', '2026-03-08 05:30:00');
    $excluded = adminAgendaAppointment($fixture, '2026-03-09 04:00:00', '2026-03-09 04:30:00');
    $range = (new AdminAgendaRange)->resolve('2026-03-08', '2026-03-09');

    expect((int) $range['from']->diffInHours($range['to']))->toBe(23);

    $response = $this->actingAs($fixture['admin'])
        ->getJson('/api/v1/admin/agenda/appointments?from=2026-03-08&to=2026-03-09')
        ->assertOk();

    expect(collect($response->json('data'))->pluck('id')->all())
        ->toContain($included->id)
        ->not->toContain($excluded->id);
});

it('resolves a fall-back business day as a 25-hour UTC range and filters appointments', function (): void {
    $fixture = adminAgendaFixture('America/New_York');
    $included = adminAgendaAppointment($fixture, '2026-11-01 04:30:00', '2026-11-01 05:30:00');
    $excluded = adminAgendaAppointment($fixture, '2026-11-02 05:00:00', '2026-11-02 05:30:00');
    $range = (new AdminAgendaRange)->resolve('2026-11-01', '2026-11-02');

    expect((int) $range['from']->diffInHours($range['to']))->toBe(25);

    $response = $this->actingAs($fixture['admin'])
        ->getJson('/api/v1/admin/agenda/appointments?from=2026-11-01&to=2026-11-02')
        ->assertOk();

    expect(collect($response->json('data'))->pluck('id')->all())
        ->toContain($included->id)
        ->not->toContain($excluded->id);
});

it('orders same-start agenda appointments by id after starts_at', function (): void {
    $fixture = adminAgendaFixture();
    $first = adminAgendaAppointment($fixture, '2026-01-05 10:00:00', '2026-01-05 11:00:00');
    $second = Appointment::factory()->create([
        'customer_id' => Customer::factory()->create()->id,
        'service_id' => $fixture['service']->id,
        'professional_id' => $fixture['professional']->id,
        'starts_at' => '2026-01-05 10:00:00',
        'ends_at' => '2026-01-05 11:00:00',
        'duration_minutes' => 60,
        'status' => AppointmentStatus::CONFIRMED,
    ]);

    $ids = $this->actingAs($fixture['admin'])
        ->getJson('/api/v1/admin/agenda/appointments?from=2026-01-05&to=2026-01-06')
        ->assertOk()
        ->json('data');

    expect(collect($ids)->pluck('id')->all())->toContain($first->id, $second->id)
        ->and(array_search($first->id, array_column($ids, 'id'), true))
        ->toBeLessThan(array_search($second->id, array_column($ids, 'id'), true));
});

it('returns detail with phone and ordered focused history', function (): void {
    $fixture = adminAgendaFixture();
    $appointment = adminAgendaAppointment($fixture, '2026-01-05 10:00:00', '2026-01-05 11:00:00');
    $appointment->history()->create([
        'event_type' => AppointmentHistoryEventType::CREATED,
        'to_status' => AppointmentStatus::CONFIRMED,
        'new_starts_at' => CarbonImmutable::parse('2026-01-05 10:00:00', 'UTC'),
        'new_ends_at' => CarbonImmutable::parse('2026-01-05 11:00:00', 'UTC'),
        'new_professional_id' => $fixture['professional']->id,
    ]);

    $this->actingAs($fixture['admin'])
        ->getJson("/api/v1/admin/agenda/appointments/{$appointment->id}")
        ->assertOk()
        ->assertJsonPath('data.customer.phone', $fixture['customer']->phone)
        ->assertJsonPath('data.history.0.event_type', 'created')
        ->assertJsonMissingPath('data.customer.phone_normalized');
});

it('returns bounded existing Customer lookup results', function (): void {
    $fixture = adminAgendaFixture();
    Customer::factory()->create(['name' => 'Ana Agenda', 'phone' => '+529999999999']);

    $this->actingAs($fixture['admin'])
        ->getJson('/api/v1/admin/agenda/customers?q=Ana')
        ->assertOk()->assertJsonPath('data.0.name', 'Ana Agenda')
        ->assertJsonMissingPath('data.0.phone_normalized');
});

it('returns active compatible Service and Professional lookups only', function (): void {
    $fixture = adminAgendaFixture();
    $inactiveCategory = ServiceCategory::factory()->create(['active' => false]);
    Service::factory()->create(['service_category_id' => $inactiveCategory->id, 'active' => true]);
    $inactiveProfessional = Professional::factory()->create(['active' => false]);
    $inactiveProfessional->services()->attach($fixture['service']);

    $this->actingAs($fixture['admin'])
        ->getJson('/api/v1/admin/agenda/services')->assertOk()
        ->assertJsonPath('data.0.id', $fixture['service']->id);

    $this->getJson("/api/v1/admin/agenda/professionals?service_id={$fixture['service']->id}")
        ->assertOk()->assertJsonPath('data.0.id', $fixture['professional']->id)
        ->assertJsonMissing(['id' => $inactiveProfessional->id]);
});

it('creates an Appointment through the authoritative Action and returns detail', function (): void {
    $fixture = adminAgendaFixture();

    $this->actingAs($fixture['admin'])
        ->postJson('/api/v1/admin/agenda/appointments', [
            'customer_id' => $fixture['customer']->id,
            'service_id' => $fixture['service']->id,
            'professional_id' => $fixture['professional']->id,
            'starts_at' => '2026-01-05T10:00:00Z',
            'ends_at' => '2026-01-05T11:00:00Z',
            'status' => 'cancelled',
            'duration_minutes' => 1,
        ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.duration_minutes', 60);
});

it('rejects ambiguous mutation timestamps before reaching the domain Action', function (): void {
    $fixture = adminAgendaFixture();

    $this->actingAs($fixture['admin'])
        ->postJson('/api/v1/admin/agenda/appointments', [
            'customer_id' => $fixture['customer']->id,
            'service_id' => $fixture['service']->id,
            'professional_id' => $fixture['professional']->id,
            'starts_at' => '2026-01-05T10:00:00',
            'ends_at' => '2026-01-05T11:00:00',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('starts_at');
});

it('maps create domain availability conflicts to 409 without partial persistence', function (): void {
    $fixture = adminAgendaFixture();
    $create = new CreateAppointment;
    $create->execute($fixture['customer'], $fixture['service'], $fixture['professional'], CarbonImmutable::parse('2026-01-05 10:00:00', 'UTC'), CarbonImmutable::parse('2026-01-05 11:00:00', 'UTC'));

    $this->actingAs($fixture['admin'])
        ->postJson('/api/v1/admin/agenda/appointments', [
            'customer_id' => Customer::factory()->create()->id,
            'service_id' => $fixture['service']->id,
            'professional_id' => $fixture['professional']->id,
            'starts_at' => '2026-01-05T10:30:00Z',
            'ends_at' => '2026-01-05T11:30:00Z',
        ])
        ->assertStatus(409)
        ->assertJsonPath('code', 'appointment_unavailable');

    expect(Appointment::query()->count())->toBe(1);
});

it('uses fresh Service duration authority when the client submits a stale interval', function (): void {
    $fixture = adminAgendaFixture();
    $fixture['service']->update(['duration_minutes' => 90]);

    $this->actingAs($fixture['admin'])
        ->postJson('/api/v1/admin/agenda/appointments', [
            'customer_id' => $fixture['customer']->id,
            'service_id' => $fixture['service']->id,
            'professional_id' => $fixture['professional']->id,
            'starts_at' => '2026-01-05T10:00:00Z',
            'ends_at' => '2026-01-05T11:00:00Z',
            'duration_minutes' => 999,
        ])
        ->assertStatus(409)
        ->assertJsonPath('code', 'appointment_unavailable');

    expect(Appointment::query()->count())->toBe(0);
});

it('reschedules through the authoritative Action using historical duration', function (): void {
    $fixture = adminAgendaFixture();
    $appointment = (new CreateAppointment)->execute(
        $fixture['customer'],
        $fixture['service'],
        $fixture['professional'],
        CarbonImmutable::parse('2026-01-05 10:00:00', 'UTC'),
        CarbonImmutable::parse('2026-01-05 11:00:00', 'UTC'),
    );
    $fixture['service']->update(['duration_minutes' => 90]);

    $this->actingAs($fixture['admin'])
        ->postJson("/api/v1/admin/agenda/appointments/{$appointment->id}/reschedule", [
            'professional_id' => $fixture['professional']->id,
            'starts_at' => '2026-01-05T12:00:00Z',
            'ends_at' => '2026-01-05T13:00:00Z',
            'customer_id' => Customer::factory()->create()->id,
            'service_id' => Service::factory()->create()->id,
            'duration_minutes' => 90,
            'status' => 'cancelled',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.duration_minutes', 60)
        ->assertJsonPath('data.history.1.event_type', 'rescheduled');
});

it('maps terminal reschedule conflicts to 409', function (): void {
    $fixture = adminAgendaFixture();
    $appointment = (new CreateAppointment)->execute(
        $fixture['customer'],
        $fixture['service'],
        $fixture['professional'],
        CarbonImmutable::parse('2026-01-05 10:00:00', 'UTC'),
        CarbonImmutable::parse('2026-01-05 11:00:00', 'UTC'),
    );
    $appointment->update(['status' => AppointmentStatus::CANCELLED]);

    $this->actingAs($fixture['admin'])
        ->postJson("/api/v1/admin/agenda/appointments/{$appointment->id}/reschedule", [
            'professional_id' => $fixture['professional']->id,
            'starts_at' => '2026-01-05T12:00:00Z',
            'ends_at' => '2026-01-05T13:00:00Z',
        ])
        ->assertStatus(409)
        ->assertJsonPath('code', 'appointment_state_conflict');
});
