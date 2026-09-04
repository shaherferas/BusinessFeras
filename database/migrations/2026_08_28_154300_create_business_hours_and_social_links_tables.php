<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_hours', function (Blueprint $table) {
            $table->id(); $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); $table->time('opens_at')->nullable(); $table->time('closes_at')->nullable();
            $table->boolean('is_closed')->default(false); $table->timestamps(); $table->unique(['business_id', 'day_of_week']);
        });
        Schema::create('business_social_links', function (Blueprint $table) {
            $table->id(); $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('platform'); $table->string('url', 2048); $table->unsignedSmallInteger('sort_order')->default(0); $table->timestamps();
            $table->unique(['business_id', 'platform']);
        });
    }
    public function down(): void { Schema::dropIfExists('business_social_links'); Schema::dropIfExists('business_hours'); }
};
