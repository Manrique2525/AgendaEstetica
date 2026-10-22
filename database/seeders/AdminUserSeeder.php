<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed a default administrator account for local and demo environments.
     *
     * The default credentials are admin@demo.com / password. Running the seeder
     * again resets the password, so it can be used to regenerate access.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Administrador Demo',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ],
        );
    }
}
