<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('businesses', 'title')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->string('title')->nullable()->after('slug');
                $table->json('description_translations')->nullable()->after('description');
            });

            // Migrate existing data to English
            DB::statement('UPDATE businesses SET description_translations = JSON_OBJECT("en", description) WHERE description IS NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['title', 'description_translations']);
        });
    }
};
