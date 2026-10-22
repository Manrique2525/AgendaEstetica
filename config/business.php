<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Business profile defaults
    |--------------------------------------------------------------------------
    |
    | Values used to create the singleton business profile when it does not
    | exist yet. They are configuration, not invented marketing data: the
    | confirmed reference hours and capacity live in the business context
    | document, and the timezone must be provided by the operator. Existing
    | records are never overwritten by the seeder.
    |
    */

    'name' => env('BUSINESS_NAME', 'Salón y Barbería Yaris'),

    'phone' => env('BUSINESS_PHONE', ''),

    'timezone' => env('BUSINESS_TIMEZONE', 'America/Mexico_City'),

    'max_simultaneous_clients' => (int) env('BUSINESS_MAX_SIMULTANEOUS_CLIENTS', 8),

    /*
    | Reference opening hours: each weekday opens at 10:00 and closes at 20:00.
    | Set BUSINESS_OPENING_HOURS=off to skip seeding hours.
    */
    'hours' => [
        'opens_at' => env('BUSINESS_OPENS_AT', '10:00'),
        'closes_at' => env('BUSINESS_CLOSES_AT', '20:00'),
        'weekdays' => [1, 2, 3, 4, 5, 6, 7],
    ],
];
