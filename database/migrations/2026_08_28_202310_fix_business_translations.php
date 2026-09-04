<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Drop unnecessary translation columns
            if (Schema::hasColumn('businesses', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('businesses', 'description_translations')) {
                $table->dropColumn('description_translations');
            }

            // Add proper translation columns for name and description
            if (!Schema::hasColumn('businesses', 'name_translations')) {
                $table->json('name_translations')->nullable()->after('name');
            }
        });

        // Migrate existing name data to name_translations
        if (Schema::hasColumn('businesses', 'name_translations')) {
            DB::statement('UPDATE businesses SET name_translations = JSON_OBJECT("en", name) WHERE name IS NOT NULL AND name_translations IS NULL');
        }
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('title')->nullable()->after('slug');
            $table->json('description_translations')->nullable()->after('description');
            $table->dropColumn('name_translations');
        });
    }
};
