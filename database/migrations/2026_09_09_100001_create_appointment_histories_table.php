<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->restrictOnDelete();
            $table->string('event_type', 32);
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32)->nullable();
            $table->dateTime('old_starts_at')->nullable();
            $table->dateTime('old_ends_at')->nullable();
            $table->dateTime('new_starts_at')->nullable();
            $table->dateTime('new_ends_at')->nullable();
            $table->foreignId('old_professional_id')->nullable()->constrained('professionals')->restrictOnDelete();
            $table->foreignId('new_professional_id')->nullable()->constrained('professionals')->restrictOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index(['appointment_id', 'created_at'], 'appointment_histories_appointment_created_index');
        });

        DB::statement("ALTER TABLE appointment_histories ADD CONSTRAINT appointment_histories_event_type_check CHECK (event_type IN ('created', 'status_changed', 'rescheduled'))");
        DB::statement("ALTER TABLE appointment_histories ADD CONSTRAINT appointment_histories_from_status_check CHECK (from_status IS NULL OR from_status IN ('confirmed', 'cancelled', 'completed', 'no_show'))");
        DB::statement("ALTER TABLE appointment_histories ADD CONSTRAINT appointment_histories_to_status_check CHECK (to_status IS NULL OR to_status IN ('confirmed', 'cancelled', 'completed', 'no_show'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_histories');
    }
};
