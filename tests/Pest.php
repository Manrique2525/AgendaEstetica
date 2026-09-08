<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature', 'Unit');

pest()->use(RefreshDatabase::class)->in('Feature');

beforeEach(function (): void {
    if (config('database.default') !== 'mysql'
        || config('database.connections.mysql.database') !== 'agenda_estetica_test') {
        throw new RuntimeException('Automated tests require the agenda_estetica_test database.');
    }
})->in('Feature');
