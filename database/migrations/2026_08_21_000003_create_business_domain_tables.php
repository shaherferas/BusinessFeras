<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', fn (Blueprint $t) => [$t->id(), $t->string('name'), $t->string('slug')->unique(), $t->string('icon')->nullable(), $t->boolean('is_active')->default(true), $t->timestamps()]);
        Schema::create('subcategories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('businesses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('phone_number');
            $t->string('whatsapp_number')->nullable();
            $t->foreignId('category_id')->constrained()->restrictOnDelete();
            $t->foreignId('subcategory_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('latitude', 10, 8);
            $t->decimal('longitude', 11, 8);
            $t->text('address_text');
            $t->timestamp('expires_at')->nullable();
            $t->enum('status', ['active', 'expired', 'suspended'])->default('active');
            $t->decimal('average_rating', 3, 2)->default(0);
            $t->timestamps();
            $t->index(['status', 'latitude', 'longitude']);
        });
        Schema::create('media_posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->enum('type', ['reel', 'story', 'post']);
            $t->string('file_path');
            $t->string('thumbnail_path')->nullable();
            $t->text('caption')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->unsignedInteger('likes_count')->default(0);
            $t->unsignedInteger('comments_count')->default(0);
            $t->unsignedInteger('views_count')->default(0);
            $t->timestamps();
        });
        Schema::create('reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('rating');
            $t->text('comment')->nullable();
            $t->timestamps();
            $t->unique(['business_id', 'user_id']);
        });
        Schema::create('interactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->enum('action_type', ['call_click', 'map_click', 'message_click']);
            $t->timestamp('created_at')->useCurrent();
            $t->index(['business_id', 'action_type', 'created_at']);
        });
    }

    public function down(): void
    {
        foreach (['interactions', 'reviews', 'media_posts', 'businesses', 'subcategories', 'categories'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
