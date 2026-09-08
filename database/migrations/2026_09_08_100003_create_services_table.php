<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_minutes');
            $table->string('pricing_type', 32);
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['service_category_id', 'name'], 'services_category_name_unique');
        });

        DB::statement('ALTER TABLE services ADD CONSTRAINT services_duration_check CHECK (duration_minutes > 0)');
        DB::statement('ALTER TABLE services ADD CONSTRAINT services_pricing_check CHECK ((pricing_type IN (\'fixed\', \'starting_from\') AND price IS NOT NULL AND price >= 0) OR (pricing_type = \'variable\' AND price IS NULL))');
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
