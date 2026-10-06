<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professional_time_off', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('professional_id')->constrained()->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->timestamps();

            $table->unique([
                'professional_id',
                'starts_at',
                'ends_at',
            ], 'professional_time_off_exact_interval_unique');
        });

        DB::statement('ALTER TABLE professional_time_off ADD CONSTRAINT professional_time_off_time_order_check CHECK (starts_at < ends_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_time_off');
    }
};
