<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProfessionalTimeOffFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['professional_id', 'starts_at', 'ends_at'])]
class ProfessionalTimeOff extends Model
{
    protected $table = 'professional_time_off';

    /** @use HasFactory<ProfessionalTimeOffFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'professional_id' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }
}
