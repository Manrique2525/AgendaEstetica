<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Services and their categories are safe to seed idempotently. No product
     * records are created because the business has not confirmed real products
     * or inventory. Administrator accounts are created exclusively through the
     * `admin:create` command, never through a seeder.
     */
    public function run(): void
    {
        $this->call([
            ServiceCategorySeeder::class,
            ServiceCatalogSeeder::class,
        ]);

        if (! app()->environment('production')) {
            $this->call(BookingDemoSeeder::class);
        }
    }
}
