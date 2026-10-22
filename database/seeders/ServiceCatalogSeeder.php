<?php

namespace Database\Seeders;

use App\Enums\ServicePricingType;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceCatalogSeeder extends Seeder
{
    /**
     * Initial catalogue provided for the academic deliverable. Prices and
     * durations are orientative and must be confirmed by the business before
     * they are published as official values.
     *
     * @var array<int, array{name: string, category: string, description: string, price: string, duration: int}>
     */
    private const SERVICES = [
        ['name' => 'Corte', 'category' => 'Cabello', 'description' => 'Corte de cabello adaptado al estilo y las preferencias de cada persona.', 'price' => '250.00', 'duration' => 45],
        ['name' => 'Tinte', 'category' => 'Coloración', 'description' => 'Aplicación de coloración para renovar el tono del cabello.', 'price' => '900.00', 'duration' => 120],
        ['name' => 'Mechas', 'category' => 'Coloración', 'description' => 'Técnica de coloración por secciones para aportar dimensión al cabello.', 'price' => '1400.00', 'duration' => 180],
        ['name' => 'Luces', 'category' => 'Coloración', 'description' => 'Iluminación de zonas seleccionadas del cabello mediante coloración.', 'price' => '1200.00', 'duration' => 150],
        ['name' => 'Balayage', 'category' => 'Coloración', 'description' => 'Degradado de color aplicado a mano para un efecto natural.', 'price' => '1800.00', 'duration' => 180],
        ['name' => 'Peinado', 'category' => 'Cabello', 'description' => 'Peinado para ocasiones especiales o uso diario.', 'price' => '450.00', 'duration' => 60],
        ['name' => 'Alaciado', 'category' => 'Tratamientos capilares', 'description' => 'Servicio de alaciado temporal del cabello.', 'price' => '1200.00', 'duration' => 120],
        ['name' => 'Rizado permanente', 'category' => 'Tratamientos capilares', 'description' => 'Servicio de rizado de larga duración.', 'price' => '1500.00', 'duration' => 150],
        ['name' => 'Tratamientos capilares', 'category' => 'Tratamientos capilares', 'description' => 'Tratamiento de acondicionamiento para el cabello.', 'price' => '700.00', 'duration' => 90],
        ['name' => 'Maquillaje', 'category' => 'Belleza', 'description' => 'Maquillaje adaptado a la ocasión y las preferencias de la persona.', 'price' => '600.00', 'duration' => 60],
        ['name' => 'Cejas', 'category' => 'Belleza', 'description' => 'Diseño y perfilado de cejas.', 'price' => '200.00', 'duration' => 30],
        ['name' => 'Uñas', 'category' => 'Uñas y pedicure', 'description' => 'Cuidado y esmaltado de uñas de las manos.', 'price' => '350.00', 'duration' => 60],
        ['name' => 'Pestañas', 'category' => 'Belleza', 'description' => 'Servicio de embellecimiento de pestañas.', 'price' => '650.00', 'duration' => 90],
        ['name' => 'Pedicure', 'category' => 'Uñas y pedicure', 'description' => 'Cuidado y esmaltado de uñas de los pies.', 'price' => '400.00', 'duration' => 60],
        ['name' => 'Barbería', 'category' => 'Barbería', 'description' => 'Servicio de barbería para corte y arreglo de barba.', 'price' => '200.00', 'duration' => 40],
    ];

    public function run(): void
    {
        $categories = ServiceCategory::query()->get()->keyBy('name');

        foreach (self::SERVICES as $service) {
            $category = $categories->get($service['category']);

            if ($category === null) {
                continue;
            }

            Service::query()->firstOrCreate(
                ['slug' => Str::slug($service['name'])],
                [
                    'service_category_id' => $category->id,
                    'name' => $service['name'],
                    'description' => $service['description'],
                    'duration_minutes' => $service['duration'],
                    'pricing_type' => ServicePricingType::FIXED,
                    'price' => $service['price'],
                    'active' => true,
                ],
            );
        }
    }
}
