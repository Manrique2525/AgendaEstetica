<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->foreignId('professional_id')->constrained()->restrictOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedInteger('duration_minutes');
            $table->string('status', 32);
            $table->timestamps();

            $table->index(
                ['professional_id', 'status', 'starts_at', 'ends_at'],
                'appointments_professional_status_time_index',
            );
            $table->index(
                ['status', 'starts_at', 'ends_at'],
                'appointments_status_time_index',
            );
        });

        DB::statement('ALTER TABLE appointments ADD CONSTRAINT appointments_time_order_check CHECK (starts_at < ends_at)');
        DB::statement('ALTER TABLE appointments ADD CONSTRAINT appointments_duration_check CHECK (duration_minutes > 0)');
        DB::statement("ALTER TABLE appointments ADD CONSTRAINT appointments_status_check CHECK (status IN ('confirmed', 'cancelled', 'completed', 'no_show'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
