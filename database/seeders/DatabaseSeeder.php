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
     * or inventory. Real administrator accounts are created through the
     * `admin:create` command; the default demo administrator is seeded only
     * outside production for local and demo environments.
     */
    public function run(): void
    {
        $this->call([
            ServiceCategorySeeder::class,
            ServiceCatalogSeeder::class,
            BusinessProfileSeeder::class,
        ]);

        if (! app()->environment('production')) {
            $this->call(AdminUserSeeder::class);
            $this->call(BookingDemoSeeder::class);
        }
    }
}
