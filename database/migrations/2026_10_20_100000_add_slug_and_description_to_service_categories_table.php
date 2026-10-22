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
        Schema::table('service_categories', function (Blueprint $table): void {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->text('description')->nullable()->after('slug');
        });

        $used = [];

        DB::table('service_categories')->orderBy('id')->get(['id', 'name'])->each(function ($category) use (&$used): void {
            $base = Str::slug((string) $category->name) ?: 'categoria-'.$category->id;
            $slug = $base;
            $suffix = 2;

            while (isset($used[$slug])) {
                $slug = $base.'-'.$suffix;
                $suffix++;
            }

            $used[$slug] = true;

            DB::table('service_categories')->where('id', $category->id)->update(['slug' => $slug]);
        });
    }

    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'description']);
        });
    }
};
