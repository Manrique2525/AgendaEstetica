<?php

use App\Enums\ServicePricingType;
use App\Models\BusinessProfile;
use App\Models\Professional;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('protects the singleton key and profile deletion at database level', function (): void {
    $candidate = new BusinessProfile;
    $candidate->fill(['singleton_key' => 2]);

    $profile = BusinessProfile::factory()->create();
    $profile->hours()->create([
        'weekday' => 1,
        'interval_order' => 1,
        'opens_at' => '09:00',
        'closes_at' => '18:00',
    ]);

    expect($candidate->getAttributes())->not->toHaveKey('singleton_key')
        ->and($profile->refresh()->singleton_key)->toBe(1)
        ->and(Schema::hasColumn('business_profiles', 'active'))->toBeFalse()
        ->and(Schema::hasColumn('business_profiles', 'deleted_at'))->toBeFalse();

    expect(fn () => $profile->delete())->toThrow(QueryException::class);
});

it('rejects orphaned business hours and restricts referenced category deletion', function (): void {
    $businessHoursIndexes = collect(Schema::getIndexes('business_hours'))
        ->pluck('name')
        ->all();

    expect($businessHoursIndexes)
        ->toContain('business_hours_exact_interval_unique')
        ->not->toContain('business_hours_business_profile_id_weekday_index');

    expect(fn () => DB::table('business_hours')->insert([
        'business_profile_id' => 999999,
        'weekday' => 1,
        'interval_order' => 1,
        'opens_at' => '09:00',
        'closes_at' => '18:00',
    ]))->toThrow(QueryException::class);

    $category = ServiceCategory::factory()->create();
    Service::factory()->create(['service_category_id' => $category->id]);

    expect(fn () => $category->delete())->toThrow(QueryException::class);
});

it('keeps category and active states independent from services and compatibility', function (): void {
    $category = ServiceCategory::factory()->create();
    $service = Service::factory()->create([
        'service_category_id' => $category->id,
        'active' => true,
    ]);
    $professional = Professional::factory()->create(['active' => true]);
    $professional->services()->attach($service);

    $category->update(['active' => false]);
    $service->update(['active' => false]);
    $professional->update(['active' => false]);

    expect($category->refresh()->active)->toBeFalse()
        ->and($service->refresh()->active)->toBeFalse()
        ->and($professional->refresh()->active)->toBeFalse()
        ->and($professional->services()->whereKey($service)->exists())->toBeTrue();
});

it('cascades dependent compatibility rows only when a service or professional is deleted', function (): void {
    $professional = Professional::factory()->create();
    $service = Service::factory()->create();
    $professional->services()->attach($service);

    $service->delete();

    expect(DB::table('professional_service')
        ->where('professional_id', $professional->id)
        ->where('service_id', $service->id)
        ->exists())->toBeFalse();

    $secondService = Service::factory()->create();
    $secondProfessional = Professional::factory()->create();
    $secondProfessional->services()->attach($secondService);

    $secondProfessional->delete();

    expect(DB::table('professional_service')
        ->where('professional_id', $secondProfessional->id)
        ->where('service_id', $secondService->id)
        ->exists())->toBeFalse();
});

it('round trips every pricing enum value through MySQL and Eloquent', function (): void {
    $category = ServiceCategory::factory()->create();
    $cases = [
        [ServicePricingType::FIXED, '100.00'],
        [ServicePricingType::STARTING_FROM, '75.00'],
        [ServicePricingType::VARIABLE, null],
    ];

    foreach ($cases as $index => [$type, $price]) {
        $service = Service::factory()->create([
            'service_category_id' => $category->id,
            'name' => "Pricing round trip {$index}",
            'pricing_type' => $type,
            'price' => $price,
        ]);

        expect($service->refresh()->pricing_type)->toBe($type);
    }
});

it('keeps customer persistence minimal and privacy bounded', function (): void {
    $columns = Schema::getColumnListing('customers');

    expect($columns)->toBe([
        'id',
        'name',
        'phone',
        'phone_normalized',
        'created_at',
        'updated_at',
    ]);
});
