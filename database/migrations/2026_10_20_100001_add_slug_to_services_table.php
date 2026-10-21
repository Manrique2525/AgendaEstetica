<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        $used = [];

        DB::table('services')->orderBy('id')->get(['id', 'name'])->each(function ($service) use (&$used): void {
            $base = Str::slug((string) $service->name) ?: 'servicio-'.$service->id;
            $slug = $base;
            $suffix = 2;

            while (isset($used[$slug])) {
                $slug = $base.'-'.$suffix;
                $suffix++;
            }

            $used[$slug] = true;

            DB::table('services')->where('id', $service->id)->update(['slug' => $slug]);
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
