<?php

use App\Enums\ServicePricingType;
use App\Models\BusinessHour;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('enforces one business profile and positive capacity', function (): void {
    $profile = BusinessProfile::factory()->create();

    expect($profile->singleton_key)->toBe(1)
        ->and($profile->max_simultaneous_clients)->toBe(8);

    expect(fn () => BusinessProfile::factory()->create())
        ->toThrow(QueryException::class);
});

it('enforces business profile guards at database level', function (): void {
    $profile = BusinessProfile::factory()->create();

    expect(fn () => DB::table('business_profiles')->insert([
        'singleton_key' => 2,
        'name' => 'Invalid profile',
        'phone' => '+525555555555',
        'timezone' => 'UTC',
        'max_simultaneous_clients' => 1,
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('business_profiles')->insert([
        'singleton_key' => 1,
        'name' => 'Duplicate profile',
        'phone' => '+525555555556',
        'timezone' => 'UTC',
        'max_simultaneous_clients' => 1,
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('business_profiles')->insert([
        'singleton_key' => 1,
        'name' => 'Invalid capacity profile',
        'phone' => '+525555555557',
        'timezone' => 'UTC',
        'max_simultaneous_clients' => 0,
    ]))->toThrow(QueryException::class);

    expect($profile->exists)->toBeTrue();
});

it('enforces valid business-hour weekdays and times while allowing multiple intervals', function (): void {
    $profile = BusinessProfile::factory()->create();

    BusinessHour::factory()->create([
        'business_profile_id' => $profile->id,
        'weekday' => 1,
        'interval_order' => 1,
        'opens_at' => '09:00',
        'closes_at' => '13:00',
    ]);
    BusinessHour::factory()->create([
        'business_profile_id' => $profile->id,
        'weekday' => 1,
        'interval_order' => 2,
        'opens_at' => '16:00',
        'closes_at' => '20:00',
    ]);
    BusinessHour::factory()->create([
        'business_profile_id' => $profile->id,
        'weekday' => 1,
        'interval_order' => 3,
        'opens_at' => '12:00',
        'closes_at' => '17:00',
    ]);

    expect($profile->hours()->count())->toBe(3);

    expect(fn () => BusinessHour::factory()->create([
        'business_profile_id' => $profile->id,
        'weekday' => 0,
    ]))->toThrow(QueryException::class);

    expect(fn () => BusinessHour::factory()->create([
        'business_profile_id' => $profile->id,
        'weekday' => 2,
        'opens_at' => '20:00',
        'closes_at' => '10:00',
    ]))->toThrow(QueryException::class);

    expect(fn () => BusinessHour::factory()->create([
        'business_profile_id' => $profile->id,
        'weekday' => 1,
        'interval_order' => 4,
        'opens_at' => '09:00',
        'closes_at' => '13:00',
    ]))->toThrow(QueryException::class);

    expect(fn () => BusinessHour::factory()->create([
        'business_profile_id' => $profile->id,
        'weekday' => 8,
    ]))->toThrow(QueryException::class);
});

it('protects service category names and service relationships', function (): void {
    $category = ServiceCategory::factory()->create(['name' => 'Technical category']);
    $service = Service::factory()->create(['service_category_id' => $category->id, 'name' => 'Technical service']);
    $professional = Professional::factory()->create();

    $professional->services()->attach($service);

    expect($category->services->first()->is($service))->toBeTrue()
        ->and($service->professionals->first()->is($professional))->toBeTrue();

    expect(fn () => ServiceCategory::factory()->create(['name' => 'Technical category']))
        ->toThrow(QueryException::class);

    expect(fn () => Service::factory()->create([
        'service_category_id' => $category->id,
        'name' => 'Technical service',
    ]))->toThrow(QueryException::class);

    expect(fn () => $professional->services()->attach($service))
        ->toThrow(QueryException::class);
});

it('enforces service duration and pricing invariants', function (): void {
    $category = ServiceCategory::factory()->create();
    $secondCategory = ServiceCategory::factory()->create();

    $fixed = Service::factory()->create([
        'service_category_id' => $category->id,
        'pricing_type' => ServicePricingType::FIXED,
        'price' => '125.00',
    ]);
    $variable = Service::factory()->create([
        'service_category_id' => $category->id,
        'pricing_type' => ServicePricingType::VARIABLE,
        'price' => null,
    ]);
    $startingFrom = Service::factory()->create([
        'service_category_id' => $category->id,
        'name' => 'Starting from service',
        'pricing_type' => ServicePricingType::STARTING_FROM,
        'price' => '75.00',
    ]);
    $sameNameOtherCategory = Service::factory()->create([
        'service_category_id' => $secondCategory->id,
        'name' => $fixed->name,
    ]);

    expect($fixed->pricing_type)->toBe(ServicePricingType::FIXED)
        ->and($variable->pricing_type)->toBe(ServicePricingType::VARIABLE)
        ->and($startingFrom->pricing_type)->toBe(ServicePricingType::STARTING_FROM)
        ->and($sameNameOtherCategory->name)->toBe($fixed->name);

    expect(fn () => Service::factory()->create([
        'service_category_id' => $category->id,
        'duration_minutes' => 0,
    ]))->toThrow(QueryException::class);

    expect(fn () => Service::factory()->create([
        'service_category_id' => $category->id,
        'pricing_type' => ServicePricingType::FIXED,
        'price' => null,
    ]))->toThrow(QueryException::class);

    expect(fn () => Service::factory()->create([
        'service_category_id' => $category->id,
        'pricing_type' => ServicePricingType::VARIABLE,
        'price' => '50.00',
    ]))->toThrow(QueryException::class);

    expect(fn () => Service::factory()->create([
        'service_category_id' => $category->id,
        'pricing_type' => ServicePricingType::STARTING_FROM,
        'price' => null,
    ]))->toThrow(QueryException::class);

    expect(fn () => Service::factory()->create([
        'service_category_id' => $category->id,
        'pricing_type' => ServicePricingType::FIXED,
        'price' => '-1.00',
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('services')->insert([
        'service_category_id' => $category->id,
        'name' => 'Unknown pricing service',
        'duration_minutes' => 60,
        'pricing_type' => 'unknown',
        'price' => '10.00',
        'active' => true,
    ]))->toThrow(QueryException::class);
});

it('enforces service foreign keys and category casts', function (): void {
    $category = ServiceCategory::factory()->create(['active' => false]);

    expect($category->active)->toBeFalse();

    expect(fn () => DB::table('services')->insert([
        'service_category_id' => 999999,
        'name' => 'Orphan service',
        'duration_minutes' => 60,
        'pricing_type' => 'variable',
        'price' => null,
        'active' => true,
    ]))->toThrow(QueryException::class);
});

it('enforces professional casts, pivot foreign keys and pivot schema', function (): void {
    $professional = Professional::factory()->create(['active' => false]);
    $sameName = Professional::factory()->create(['name' => $professional->name]);
    $service = Service::factory()->create();

    expect($professional->active)->toBeFalse()
        ->and($sameName->name)->toBe($professional->name);

    expect(Schema::hasColumn('professional_service', 'created_at'))->toBeFalse()
        ->and(Schema::hasColumn('professional_service', 'updated_at'))->toBeFalse();

    expect(fn () => DB::table('professional_service')->insert([
        'professional_id' => 999999,
        'service_id' => $service->id,
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('professional_service')->insert([
        'professional_id' => $professional->id,
        'service_id' => 999999,
    ]))->toThrow(QueryException::class);
});

it('allows duplicate normalized customer phones without accounts', function (): void {
    $phone = '+529931234567';
    $first = Customer::factory()->create([
        'phone' => $phone,
        'phone_normalized' => $phone,
    ]);
    $second = Customer::factory()->create([
        'phone' => $phone,
        'phone_normalized' => $phone,
    ]);

    expect($first->id)->not->toBe($second->id)
        ->and(Customer::query()->where('phone_normalized', $phone)->count())->toBe(2);
});
