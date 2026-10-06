<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professionals', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('professional_service', function (Blueprint $table): void {
            $table->foreignId('professional_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->primary(['professional_id', 'service_id']);
            $table->index(['service_id', 'professional_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_service');
        Schema::dropIfExists('professionals');
    }
};
