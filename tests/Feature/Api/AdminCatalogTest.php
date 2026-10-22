<?php

use App\Enums\ServicePricingType;
use App\Models\Appointment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\ServiceCatalogSeeder;
use Database\Seeders\ServiceCategorySeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('seeds the confirmed catalogue idempotently', function (): void {
    $this->seed(ServiceCategorySeeder::class);
    $this->seed(ServiceCatalogSeeder::class);
    $this->seed(ServiceCategorySeeder::class);
    $this->seed(ServiceCatalogSeeder::class);

    expect(ServiceCategory::query()->count())->toBe(6)
        ->and(Service::query()->count())->toBe(15);

    $service = Service::query()->where('slug', 'balayage')->firstOrFail();

    expect($service->name)->toBe('Balayage')
        ->and($service->description)->not->toBeNull()
        ->and($service->duration_minutes)->toBe(180)
        ->and((string) $service->price)->toBe('1800.00')
        ->and($service->category->name)->toBe('Coloración')
        ->and($service->pricing_type)->toBe(ServicePricingType::FIXED);
});

it('does not overwrite administrator edits when the catalogue seeder runs again', function (): void {
    $this->seed(ServiceCategorySeeder::class);
    $this->seed(ServiceCatalogSeeder::class);

    $service = Service::query()->where('slug', 'corte')->firstOrFail();
    $service->update(['name' => 'Corte personalizado', 'price' => '399.00', 'active' => false]);

    $this->seed(ServiceCatalogSeeder::class);

    $service->refresh();

    expect($service->name)->toBe('Corte personalizado')
        ->and((string) $service->price)->toBe('399.00')
        ->and($service->active)->toBeFalse();
});

it('creates a service category with an automatic slug', function (): void {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->postJson('/api/v1/admin/catalog/service-categories', [
            'name' => 'Cuidado de barba',
            'description' => 'Servicios de barba.',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Cuidado de barba')
        ->assertJsonPath('data.slug', 'cuidado-de-barba')
        ->assertJsonPath('data.active', true);

    expect(ServiceCategory::query()->where('slug', 'cuidado-de-barba')->exists())->toBeTrue();
});

it('validates service payloads and pricing consistency', function (): void {
    $admin = User::factory()->admin()->create();
    $category = ServiceCategory::factory()->create();

    $this->actingAs($admin)
        ->postJson('/api/v1/admin/catalog/services', [
            'service_category_id' => $category->id,
            'name' => '',
            'duration_minutes' => 0,
            'pricing_type' => 'fixed',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'duration_minutes', 'price']);

    $this->actingAs($admin)
        ->postJson('/api/v1/admin/catalog/services', [
            'service_category_id' => $category->id,
            'name' => 'Servicio variable',
            'duration_minutes' => 30,
            'pricing_type' => 'variable',
            'price' => 100,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['price']);
});

it('creates and updates a service with slug and pricing', function (): void {
    $admin = User::factory()->admin()->create();
    $category = ServiceCategory::factory()->create();

    $created = $this->actingAs($admin)
        ->postJson('/api/v1/admin/catalog/services', [
            'service_category_id' => $category->id,
            'name' => 'Servicio nuevo',
            'description' => 'Descripción nueva.',
            'duration_minutes' => 45,
            'pricing_type' => 'starting_from',
            'price' => '500.00',
        ])
        ->assertCreated()
        ->assertJsonPath('data.slug', 'servicio-nuevo')
        ->assertJsonPath('data.category.id', $category->id)
        ->json('data');

    $this->actingAs($admin)
        ->putJson("/api/v1/admin/catalog/services/{$created['id']}", [
            'service_category_id' => $category->id,
            'name' => 'Servicio actualizado',
            'duration_minutes' => 60,
            'pricing_type' => 'fixed',
            'price' => '650.00',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Servicio actualizado')
        ->assertJsonPath('data.slug', 'servicio-actualizado')
        ->assertJsonPath('data.price', '650.00');
});

it('uploads a service image and exposes its public URL', function (): void {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $category = ServiceCategory::factory()->create();

    $created = $this->actingAs($admin)
        ->post('/api/v1/admin/catalog/services', [
            'service_category_id' => $category->id,
            'name' => 'Servicio con imagen',
            'duration_minutes' => 30,
            'pricing_type' => 'fixed',
            'price' => '300.00',
            'image' => UploadedFile::fake()->image('servicio.jpg', 600, 400),
        ])
        ->assertCreated()
        ->json('data');

    expect($created['image_url'])->not->toBeNull();

    Storage::disk('public')->assertExists(str_replace('/storage/', '', $created['image_url']));
});

it('rejects a service image with an invalid mime type', function (): void {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $category = ServiceCategory::factory()->create();

    $this->actingAs($admin)
        ->post('/api/v1/admin/catalog/services', [
            'service_category_id' => $category->id,
            'name' => 'Servicio con archivo inválido',
            'duration_minutes' => 30,
            'pricing_type' => 'fixed',
            'price' => '300.00',
            'image' => UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['image']);
});

it('toggles active status without deleting records', function (): void {
    $admin = User::factory()->admin()->create();
    $service = Service::factory()->create(['active' => true]);

    $this->actingAs($admin)
        ->patchJson("/api/v1/admin/catalog/services/{$service->id}/status", ['active' => false])
        ->assertOk()
        ->assertJsonPath('data.active', false);

    expect($service->refresh()->active)->toBeFalse();
});

it('blocks deleting a category with services and a service with appointments', function (): void {
    $admin = User::factory()->admin()->create();
    $category = ServiceCategory::factory()->create();
    $service = Service::factory()->create(['service_category_id' => $category->id]);

    $this->actingAs($admin)
        ->deleteJson("/api/v1/admin/catalog/service-categories/{$category->id}")
        ->assertStatus(409)
        ->assertJsonPath('code', 'category_has_services');

    Appointment::factory()->create(['service_id' => $service->id]);

    $this->actingAs($admin)
        ->deleteJson("/api/v1/admin/catalog/services/{$service->id}")
        ->assertStatus(409)
        ->assertJsonPath('code', 'service_has_appointments');

    expect(ServiceCategory::query()->whereKey($category->id)->exists())->toBeTrue()
        ->and(Service::query()->whereKey($service->id)->exists())->toBeTrue();
});

it('deletes a service without appointments', function (): void {
    $admin = User::factory()->admin()->create();
    $service = Service::factory()->create();

    $this->actingAs($admin)
        ->deleteJson("/api/v1/admin/catalog/services/{$service->id}")
        ->assertNoContent();

    expect(Service::query()->whereKey($service->id)->exists())->toBeFalse();
});

it('manages products and product categories', function (): void {
    $admin = User::factory()->admin()->create();

    $category = $this->actingAs($admin)
        ->postJson('/api/v1/admin/catalog/product-categories', ['name' => 'Cuidado capilar'])
        ->assertCreated()
        ->assertJsonPath('data.slug', 'cuidado-capilar')
        ->json('data');

    $product = $this->actingAs($admin)
        ->postJson('/api/v1/admin/catalog/products', [
            'product_category_id' => $category['id'],
            'name' => 'Producto de ejemplo',
            'price' => '199.00',
            'stock' => 5,
        ])
        ->assertCreated()
        ->assertJsonPath('data.category.id', $category['id'])
        ->assertJsonPath('data.active', true)
        ->json('data');

    $this->actingAs($admin)
        ->getJson('/api/v1/admin/catalog/products?q=ejemplo')
        ->assertOk()
        ->assertJsonCount(1, 'data');

    $this->actingAs($admin)
        ->putJson("/api/v1/admin/catalog/products/{$product['id']}", [
            'product_category_id' => $category['id'],
            'name' => 'Producto actualizado',
            'price' => '249.00',
        ])
        ->assertOk()
        ->assertJsonPath('data.price', '249.00');

    $this->actingAs($admin)
        ->deleteJson("/api/v1/admin/catalog/product-categories/{$category['id']}")
        ->assertStatus(409)
        ->assertJsonPath('code', 'product_category_has_products');

    $this->actingAs($admin)
        ->deleteJson("/api/v1/admin/catalog/products/{$product['id']}")
        ->assertNoContent();

    expect(Product::query()->whereKey($product['id'])->exists())->toBeFalse();
});

it('forbids non-administrators from managing the catalogue', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/v1/admin/catalog/service-categories')
        ->assertForbidden();

    $this->actingAs($user)
        ->postJson('/api/v1/admin/catalog/service-categories', ['name' => 'No permitido'])
        ->assertForbidden();
});

it('exposes active products publicly and hides inactive ones', function (): void {
    $category = ProductCategory::factory()->create(['active' => true]);
    $inactiveCategory = ProductCategory::factory()->create(['active' => false]);
    $visible = Product::factory()->create(['product_category_id' => $category->id, 'active' => true]);
    $hidden = Product::factory()->create(['product_category_id' => $category->id, 'active' => false]);
    $hiddenCategoryProduct = Product::factory()->create(['product_category_id' => $inactiveCategory->id, 'active' => true]);

    $data = $this->getJson('/api/v1/public/products')->assertOk()->json('data');

    $ids = collect($data)->pluck('id')->all();

    expect($ids)->toContain($visible->id)
        ->not->toContain($hidden->id)
        ->not->toContain($hiddenCategoryProduct->id)
        ->and($data[0]['price_display'])->toContain('$');
});
