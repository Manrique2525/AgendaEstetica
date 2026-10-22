<?php

use App\Actions\ReplaceProfessionalSchedule;
use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('persists valid schedules and supports adjacency, multiple intervals and weekdays', function (): void {
    $professional = Professional::factory()->create();

    (new ReplaceProfessionalSchedule)->execute($professional, [
        ['weekday' => 1, 'starts_at' => '09:00', 'ends_at' => '13:00'],
        ['weekday' => 1, 'starts_at' => '13:00', 'ends_at' => '17:00'],
        ['weekday' => 2, 'starts_at' => '09:00', 'ends_at' => '17:00'],
    ]);

    expect($professional->schedules()->orderBy('weekday')->orderBy('starts_at')->get()->map(fn (ProfessionalSchedule $schedule): array => [
        $schedule->weekday,
        $schedule->starts_at,
        $schedule->ends_at,
    ])->all())->toBe([
        [1, '09:00:00', '13:00:00'],
        [1, '13:00:00', '17:00:00'],
        [2, '09:00:00', '17:00:00'],
    ]);
});

it('replaces only the target Professional schedule and supports empty replacement', function (): void {
    $first = Professional::factory()->create();
    $second = Professional::factory()->create();

    ProfessionalSchedule::factory()->create([
        'professional_id' => $first->id,
        'weekday' => 1,
        'starts_at' => '09:00',
        'ends_at' => '12:00',
    ]);
    ProfessionalSchedule::factory()->create([
        'professional_id' => $second->id,
        'weekday' => 2,
        'starts_at' => '10:00',
        'ends_at' => '14:00',
    ]);

    (new ReplaceProfessionalSchedule)->execute($first, [
        ['weekday' => 3, 'starts_at' => '11:00', 'ends_at' => '15:00'],
    ]);

    expect($first->schedules()->count())->toBe(1)
        ->and($first->schedules()->first()->weekday)->toBe(3)
        ->and($second->schedules()->count())->toBe(1)
        ->and($second->schedules()->first()->weekday)->toBe(2);

    (new ReplaceProfessionalSchedule)->execute($first, []);

    expect($first->schedules()->count())->toBe(0)
        ->and($second->schedules()->count())->toBe(1);
});

it('rejects invalid schedule replacements before deleting old rows', function (): void {
    $professional = Professional::factory()->create();
    ProfessionalSchedule::factory()->create([
        'professional_id' => $professional->id,
        'weekday' => 1,
        'starts_at' => '09:00',
        'ends_at' => '17:00',
    ]);

    $invalidSchedules = [
        [
            ['weekday' => 0, 'starts_at' => '09:00', 'ends_at' => '12:00'],
        ],
        [
            ['weekday' => 8, 'starts_at' => '09:00', 'ends_at' => '12:00'],
        ],
        [
            ['weekday' => 1, 'starts_at' => '09:00', 'ends_at' => '09:00'],
        ],
        [
            ['weekday' => 1, 'starts_at' => '13:00', 'ends_at' => '12:00'],
        ],
        [
            ['weekday' => 1, 'starts_at' => '09:00', 'ends_at' => '13:00'],
            ['weekday' => 1, 'starts_at' => '12:00', 'ends_at' => '17:00'],
        ],
        [
            ['weekday' => 1, 'starts_at' => '09:00', 'ends_at' => '13:00'],
            ['weekday' => 1, 'starts_at' => '09:00', 'ends_at' => '17:00'],
        ],
    ];

    foreach ($invalidSchedules as $replacement) {
        expect(fn () => (new ReplaceProfessionalSchedule)->execute($professional, $replacement))
            ->toThrow(InvalidArgumentException::class);
        expect($professional->schedules()->count())->toBe(1)
            ->and($professional->schedules()->first()->starts_at)->toBe('09:00:00');
    }
});

it('enforces schedule database constraints and foreign keys', function (): void {
    $professional = Professional::factory()->create();
    $attributes = ProfessionalSchedule::factory()->make(['professional_id' => $professional->id])->getAttributes();

    expect(fn () => DB::table('professional_schedules')->insert([
        ...$attributes,
        'weekday' => 0,
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('professional_schedules')->insert([
        ...$attributes,
        'starts_at' => '17:00',
        'ends_at' => '09:00',
    ]))->toThrow(QueryException::class);

    ProfessionalSchedule::factory()->create(['professional_id' => $professional->id]);

    expect(fn () => ProfessionalSchedule::factory()->create([
        'professional_id' => $professional->id,
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('professional_schedules')->insert([
        ...$attributes,
        'professional_id' => 999999,
    ]))->toThrow(QueryException::class);
});
