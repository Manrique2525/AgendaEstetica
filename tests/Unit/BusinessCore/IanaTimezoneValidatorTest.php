<?php

use App\Support\IanaTimezoneValidator;

it('accepts recognized IANA timezone identifiers', function (string $timezone): void {
    expect((new IanaTimezoneValidator)->isValid($timezone))->toBeTrue();
    (new IanaTimezoneValidator)->assertValid($timezone);
})->with([
    'America/Mexico_City',
    'America/New_York',
    'Europe/Madrid',
    'UTC',
]);

it('rejects unrecognized timezone identifiers', function (string $timezone): void {
    $validator = new IanaTimezoneValidator;

    expect($validator->isValid($timezone))->toBeFalse()
        ->and(fn () => $validator->assertValid($timezone))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'America/NotARealCity',
    'Mexico/Whatever',
    'GMT+27',
    '',
    'random-text',
]);
