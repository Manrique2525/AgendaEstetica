<?php

use App\Models\Professional;
use App\Models\ProfessionalTimeOff;
use App\Support\ProfessionalTimeOffOverlapValidator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('persists valid UTC TimeOff and keeps different Professionals independent', function (): void {
    $first = Professional::factory()->create();
    $second = Professional::factory()->create();

    $timeOff = ProfessionalTimeOff::factory()->create([
        'professional_id' => $first->id,
        'starts_at' => '2026-01-15 10:00:00',
        'ends_at' => '2026-01-15 12:00:00',
    ]);
    $other = ProfessionalTimeOff::factory()->create([
        'professional_id' => $second->id,
        'starts_at' => '2026-01-15 10:00:00',
        'ends_at' => '2026-01-15 12:00:00',
    ]);

    expect($timeOff->refresh()->professional_id)->toBe($first->id)
        ->and($timeOff->starts_at->format('Y-m-d H:i:s'))->toBe('2026-01-15 10:00:00')
        ->and($other->professional_id)->toBe($second->id);
});

it('rejects invalid TimeOff database values, duplicates and orphaned Professionals', function (): void {
    $professional = Professional::factory()->create();
    $attributes = ProfessionalTimeOff::factory()->make(['professional_id' => $professional->id])->getAttributes();

    expect(fn () => DB::table('professional_time_off')->insert([
        ...$attributes,
        'starts_at' => '2026-01-15 12:00:00',
        'ends_at' => '2026-01-15 12:00:00',
    ]))->toThrow(QueryException::class);

    ProfessionalTimeOff::factory()->create(['professional_id' => $professional->id]);

    expect(fn () => ProfessionalTimeOff::factory()->create([
        'professional_id' => $professional->id,
    ]))->toThrow(QueryException::class)
        ->and(fn () => DB::table('professional_time_off')->insert([
            ...$attributes,
            'professional_id' => 999999,
        ]))->toThrow(QueryException::class);
});

it('rejects same-Professional overlap and allows adjacency or another Professional', function (): void {
    $first = Professional::factory()->create();
    $second = Professional::factory()->create();
    $validator = new ProfessionalTimeOffOverlapValidator;

    $existing = ProfessionalTimeOff::factory()->create([
        'professional_id' => $first->id,
        'starts_at' => '2026-01-15 10:00:00',
        'ends_at' => '2026-01-15 12:00:00',
    ]);

    foreach ([
        ['2026-01-15 09:00:00', '2026-01-15 11:00:00'],
        ['2026-01-15 11:00:00', '2026-01-15 13:00:00'],
        ['2026-01-15 10:00:00', '2026-01-15 12:00:00'],
        ['2026-01-15 09:00:00', '2026-01-15 13:00:00'],
        ['2026-01-15 11:00:00', '2026-01-15 12:00:00'],
    ] as [$startsAt, $endsAt]) {
        expect(fn () => $validator->assertNoOverlap($first, new DateTimeImmutable($startsAt), new DateTimeImmutable($endsAt)))
            ->toThrow(InvalidArgumentException::class);
    }

    expect(fn () => $validator->assertNoOverlap($first, new DateTimeImmutable('2026-01-15 12:00:00'), new DateTimeImmutable('2026-01-15 13:00:00')))
        ->not->toThrow(InvalidArgumentException::class)
        ->and(fn () => $validator->assertNoOverlap($second, new DateTimeImmutable('2026-01-15 10:00:00'), new DateTimeImmutable('2026-01-15 12:00:00')))
        ->not->toThrow(InvalidArgumentException::class)
        ->and($existing->exists)->toBeTrue();
});

it('rejects invalid TimeOff interval order before querying overlap', function (): void {
    $professional = Professional::factory()->create();
    $validator = new ProfessionalTimeOffOverlapValidator;

    expect(fn () => $validator->assertNoOverlap(
        $professional,
        new DateTimeImmutable('2026-01-15 12:00:00'),
        new DateTimeImmutable('2026-01-15 12:00:00'),
    ))->toThrow(InvalidArgumentException::class);
});
