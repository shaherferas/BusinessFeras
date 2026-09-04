<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('business_faqs', 'answer_translations')) {
            Schema::table('business_faqs', function (Blueprint $table) {
                $table->json('answer_translations')->nullable()->after('answer');
            });

            // Migrate existing data to English
            DB::statement('UPDATE business_faqs SET answer_translations = JSON_OBJECT("en", answer) WHERE answer IS NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('business_faqs', function (Blueprint $table) {
            $table->dropColumn('answer_translations');
        });
    }
};
