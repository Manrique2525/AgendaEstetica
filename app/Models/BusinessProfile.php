<?php

namespace App\Models;

use Database\Factories\BusinessProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'phone', 'timezone', 'max_simultaneous_clients'])]
class BusinessProfile extends Model
{
    /** @use HasFactory<BusinessProfileFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'singleton_key' => 'integer',
            'max_simultaneous_clients' => 'integer',
        ];
    }

    public function hours(): HasMany
    {
        return $this->hasMany(BusinessHour::class);
    }
}
