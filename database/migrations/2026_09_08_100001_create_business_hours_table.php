<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_hours', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_profile_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->unsignedTinyInteger('interval_order')->default(1);
            $table->time('opens_at');
            $table->time('closes_at');
            $table->timestamps();

            $table->unique([
                'business_profile_id',
                'weekday',
                'opens_at',
                'closes_at',
            ], 'business_hours_exact_interval_unique');
            $table->index(['business_profile_id', 'weekday']);
        });

        DB::statement('ALTER TABLE business_hours ADD CONSTRAINT business_hours_weekday_check CHECK (weekday BETWEEN 1 AND 7)');
        DB::statement('ALTER TABLE business_hours ADD CONSTRAINT business_hours_time_order_check CHECK (opens_at < closes_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('business_hours');
    }
};
