<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProfessionalScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['professional_id', 'weekday', 'starts_at', 'ends_at'])]
class ProfessionalSchedule extends Model
{
    /** @use HasFactory<ProfessionalScheduleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'professional_id' => 'integer',
            'weekday' => 'integer',
        ];
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }
}
