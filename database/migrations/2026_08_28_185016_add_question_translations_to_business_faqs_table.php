<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add multilingual support for BusinessFaq questions.
     */
    public function up(): void
    {
        Schema::table('business_faqs', function (Blueprint $table) {
            // Add JSON column for multilingual questions
            $table->json('question_translations')->nullable()->after('question');
        });

        // Migrate existing data to English
        DB::statement('UPDATE business_faqs SET question_translations = JSON_OBJECT("en", question) WHERE question IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_faqs', function (Blueprint $table) {
            $table->dropColumn('question_translations');
        });
    }
};
