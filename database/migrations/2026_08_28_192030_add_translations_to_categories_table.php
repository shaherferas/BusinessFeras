<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'name_translations')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->json('name_translations')->nullable()->after('name');
            });

            // Migrate existing data to English
            DB::statement('UPDATE categories SET name_translations = JSON_OBJECT("en", name) WHERE name IS NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('name_translations');
        });
    }
};
