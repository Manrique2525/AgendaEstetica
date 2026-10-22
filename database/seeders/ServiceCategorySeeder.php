<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceCategorySeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, description: string}>
     */
    private const CATEGORIES = [
        ['name' => 'Cabello', 'description' => 'Servicios de corte y peinado.'],
        ['name' => 'Coloración', 'description' => 'Servicios de color y dimensionamiento del cabello.'],
        ['name' => 'Tratamientos capilares', 'description' => 'Servicios de acondicionamiento y cambios de textura.'],
        ['name' => 'Belleza', 'description' => 'Servicios de maquillaje, cejas y pestañas.'],
        ['name' => 'Uñas y pedicure', 'description' => 'Cuidado de uñas de manos y pies.'],
        ['name' => 'Barbería', 'description' => 'Servicios de barbería.'],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            ServiceCategory::query()->firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'active' => true,
                ],
            );
        }
    }
}
