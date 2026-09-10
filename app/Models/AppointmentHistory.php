<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AppointmentHistoryEventType;
use App\Enums\AppointmentStatus;
use Database\Factories\AppointmentHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'appointment_id',
    'event_type',
    'from_status',
    'to_status',
    'old_starts_at',
    'old_ends_at',
    'new_starts_at',
    'new_ends_at',
    'old_professional_id',
    'new_professional_id',
])]
class AppointmentHistory extends Model
{
    /** @use HasFactory<AppointmentHistoryFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'appointment_id' => 'integer',
            'event_type' => AppointmentHistoryEventType::class,
            'from_status' => AppointmentStatus::class,
            'to_status' => AppointmentStatus::class,
            'old_starts_at' => 'datetime',
            'old_ends_at' => 'datetime',
            'new_starts_at' => 'datetime',
            'new_ends_at' => 'datetime',
            'old_professional_id' => 'integer',
            'new_professional_id' => 'integer',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function oldProfessional(): BelongsTo
    {
        return $this->belongsTo(Professional::class, 'old_professional_id');
    }

    public function newProfessional(): BelongsTo
    {
        return $this->belongsTo(Professional::class, 'new_professional_id');
    }
}
