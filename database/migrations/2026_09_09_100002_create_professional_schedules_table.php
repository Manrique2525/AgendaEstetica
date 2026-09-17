<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professional_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('professional_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->timestamps();

            $table->unique([
                'professional_id',
                'weekday',
                'starts_at',
                'ends_at',
            ], 'professional_schedules_exact_interval_unique');
        });

        DB::statement('ALTER TABLE professional_schedules ADD CONSTRAINT professional_schedules_weekday_check CHECK (weekday BETWEEN 1 AND 7)');
        DB::statement('ALTER TABLE professional_schedules ADD CONSTRAINT professional_schedules_time_order_check CHECK (starts_at < ends_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_schedules');
    }
};
