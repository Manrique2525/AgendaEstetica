<?php

use App\Actions\ReplaceBusinessHours;
use App\Models\BusinessHour;
use App\Models\BusinessProfile;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

it('replaces a weekly schedule atomically with valid intervals', function (): void {
    $profile = BusinessProfile::factory()->create();

    (new ReplaceBusinessHours)->execute($profile, [
        ['weekday' => 1, 'opens_at' => '09:00', 'closes_at' => '13:00'],
        ['weekday' => 1, 'opens_at' => '13:00', 'closes_at' => '18:00'],
        ['weekday' => 2, 'opens_at' => '09:00', 'closes_at' => '18:00'],
    ]);

    expect($profile->hours()->orderBy('weekday')->orderBy('interval_order')->get()->map(fn (BusinessHour $hour): array => [
        $hour->weekday,
        $hour->opens_at,
        $hour->closes_at,
        $hour->interval_order,
    ])->all())->toBe([
        [1, '09:00:00', '13:00:00', 1],
        [1, '13:00:00', '18:00:00', 2],
        [2, '09:00:00', '18:00:00', 1],
    ]);
});

it('allows an empty schedule and replaces the previous one', function (): void {
    $profile = BusinessProfile::factory()->create();
    $profile->hours()->create([
        'weekday' => 1,
        'interval_order' => 1,
        'opens_at' => '09:00',
        'closes_at' => '18:00',
    ]);

    (new ReplaceBusinessHours)->execute($profile, []);

    expect($profile->hours()->count())->toBe(0);
});

it('rejects invalid replacements before deleting the existing schedule', function (): void {
    $profile = BusinessProfile::factory()->create();
    $profile->hours()->create([
        'weekday' => 1,
        'interval_order' => 1,
        'opens_at' => '09:00',
        'closes_at' => '18:00',
    ]);

    $invalidReplacements = [
        [
            ['weekday' => 1, 'opens_at' => '09:00', 'closes_at' => '13:00'],
            ['weekday' => 1, 'opens_at' => '12:00', 'closes_at' => '17:00'],
        ],
        [
            ['weekday' => 1, 'opens_at' => '09:00', 'closes_at' => '18:00'],
            ['weekday' => 1, 'opens_at' => '10:00', 'closes_at' => '12:00'],
        ],
        [
            ['weekday' => 1, 'opens_at' => '09:00', 'closes_at' => '13:00'],
            ['weekday' => 1, 'opens_at' => '09:00', 'closes_at' => '13:00'],
        ],
        [
            ['weekday' => 1, 'opens_at' => '09:00', 'closes_at' => '13:00'],
            ['weekday' => 1, 'opens_at' => '09:00', 'closes_at' => '17:00'],
        ],
        [
            ['weekday' => 1, 'opens_at' => '09:00', 'closes_at' => '17:00'],
            ['weekday' => 1, 'opens_at' => '13:00', 'closes_at' => '17:00'],
        ],
        [['weekday' => 0, 'opens_at' => '09:00', 'closes_at' => '13:00']],
        [['weekday' => 8, 'opens_at' => '09:00', 'closes_at' => '13:00']],
        [['weekday' => 1, 'opens_at' => '13:00', 'closes_at' => '13:00']],
        [['weekday' => 1, 'opens_at' => '14:00', 'closes_at' => '13:00']],
    ];

    foreach ($invalidReplacements as $replacement) {
        expect(fn () => (new ReplaceBusinessHours)->execute($profile, $replacement))
            ->toThrow(InvalidArgumentException::class);

        expect($profile->hours()->count())->toBe(1)
            ->and($profile->hours()->first()->opens_at)->toBe('09:00:00');
    }
});

it('keeps equal times on different weekdays independent', function (): void {
    $profile = BusinessProfile::factory()->create();

    (new ReplaceBusinessHours)->execute($profile, [
        ['weekday' => 1, 'opens_at' => '09:00', 'closes_at' => '17:00'],
        ['weekday' => 2, 'opens_at' => '09:00', 'closes_at' => '17:00'],
    ]);

    expect($profile->hours()->count())->toBe(2);
});

it('uses a transaction when persistence fails after deleting old rows', function (): void {
    $profile = BusinessProfile::factory()->create();
    $profile->hours()->create([
        'weekday' => 1,
        'interval_order' => 1,
        'opens_at' => '09:00',
        'closes_at' => '18:00',
    ]);

    $dispatcher = DB::connection()->getEventDispatcher();

    DB::listen(function (QueryExecuted $query): void {
        if (str_contains(strtolower($query->sql), 'insert into `business_hours`')) {
            throw new RuntimeException('Forced persistence failure.');
        }
    });

    try {
        expect(fn () => (new ReplaceBusinessHours)->execute($profile, [
            ['weekday' => 2, 'opens_at' => '10:00', 'closes_at' => '12:00'],
        ]))->toThrow(RuntimeException::class);
    } finally {
        $dispatcher?->forget(QueryExecuted::class);
    }

    expect($profile->hours()->count())->toBe(1)
        ->and($profile->hours()->first()->weekday)->toBe(1);
});
