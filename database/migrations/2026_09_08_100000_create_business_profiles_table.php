<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('singleton_key')->default(1)->unique();
            $table->string('name');
            $table->string('phone', 32);
            $table->string('timezone', 64);
            $table->unsignedInteger('max_simultaneous_clients');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE business_profiles ADD CONSTRAINT business_profiles_singleton_key_check CHECK (singleton_key = 1)');
        DB::statement('ALTER TABLE business_profiles ADD CONSTRAINT business_profiles_capacity_check CHECK (max_simultaneous_clients > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('business_profiles');
    }
};
