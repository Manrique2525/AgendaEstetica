<?php

namespace App\Models;

use App\Enums\ServicePricingType;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['service_category_id', 'name', 'description', 'duration_minutes', 'pricing_type', 'price', 'active'])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'service_category_id' => 'integer',
            'duration_minutes' => 'integer',
            'pricing_type' => ServicePricingType::class,
            'price' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function professionals(): BelongsToMany
    {
        return $this->belongsToMany(Professional::class);
    }
}
