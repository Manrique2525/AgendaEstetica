<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $database = $_SERVER['DB_DATABASE'] ?? $_ENV['DB_DATABASE'] ?? null;

        if ($database !== 'agenda_estetica_test') {
            throw new \RuntimeException('Tests must use agenda_estetica_test.');
        }

        return parent::createApplication();
    }
}
