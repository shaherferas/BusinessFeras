<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('amenities', 'name_translations')) {
            Schema::table('amenities', function (Blueprint $table) {
                // Add JSON column for multilingual names
                $table->json('name_translations')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('amenities', 'name_translations')) {
            Schema::table('amenities', function (Blueprint $table) {
                $table->dropColumn('name_translations');
            });
        }
    }
};