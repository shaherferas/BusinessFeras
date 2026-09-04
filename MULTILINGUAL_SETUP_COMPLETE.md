# Multi-Language Titles Implementation - Complete Setup

## Overview
This document provides a complete implementation of multi-language support for all models in your Laravel application using the JSON Column approach.

## What Has Been Implemented

### 1. HasTranslations Trait
**File:** `app/Traits/HasTranslations.php`

A reusable trait that provides multilingual support for any model attribute.

**Features:**
- Get translations in any locale
- Set translations for specific locales
- Automatic fallback to default locale
- Dynamic accessors that return content in current locale

### 2. Business Model (Already Implemented)
**File:** `app/Models/Business.php`

**Changes:**
- Added `HasTranslations` trait
- Configured `title` and `description_translations` as translatable fields
- Added to `$fillable` array
- Added to `$casts` array

### 3. Migration for Business Model
**File:** `database/migrations/2026_08_28_171613_add_translatable_fields_to_businesses_table.php`

**Changes:**
- Adds `title` JSON column
- Adds `description_translations` JSON column
- Migrates existing data to English translations

---

## How to Add Multi-Language Support to Other Models

### Step 1: Create Migration

For each model you want to translate, create a migration:

```bash
php artisan make:migration add_translatable_fields_to_categories_table
```

**Example for Categories:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Add JSON column for multilingual name
            $table->json('name_translations')->nullable()->after('name');
        });

        // Migrate existing data to English
        DB::statement('UPDATE categories SET name_translations = JSON_OBJECT("en", name) WHERE name IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('name_translations');
        });
    }
};
```

### Step 2: Update Model

**Example for Category Model:**
```php
<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasTranslations;

    protected $fillable = ['name', 'slug', 'icon', 'name_translations', 'is_active'];

    protected $translatable = ['name_translations'];

    protected function casts(): array 
    { 
        return [
            'name_translations' => 'array',
            'is_active' => 'boolean'
        ]; 
    }
}
```

### Step 3: Run Migration

```bash
php artisan migrate
```

---

## Usage Examples

### Creating Records with Translations

```php
// Business with translations
$business = Business::create([
    'name' => 'my-business-slug',
    'slug' => 'my-business-slug',
    'title' => [
        'en' => 'My Business Title',
        'ar' => 'عنوان عملي'
    ],
    'description_translations' => [
        'en' => 'English description',
        'ar' => 'الوصف العربي'
    ],
    'phone_number' => '+1234567890',
    'category_id' => 1,
    'latitude' => 40.7128,
    'longitude' => -74.0060,
    'address_text' => '123 Main St',
]);

// Category with translations
$category = Category::create([
    'name' => 'food', // For slug generation
    'slug' => 'food',
    'name_translations' => [
        'en' => 'Food & Restaurants',
        'ar' => 'الطعام والمطاعم'
    ],
    'is_active' => true,
]);
```

### Getting Translations

```php
// Get title in current application locale
app()->setLocale('en');
echo $business->title; // "My Business Title"

app()->setLocale('ar');
echo $business->title; // "عنوان عملي"

// Get title in specific locale
$titleEn = $business->getTranslation('title', 'en');
$titleAr = $business->getTranslation('title', 'ar');

// Get all translations
$allTitles = $business->getTranslations('title');
// Returns: ['en' => 'My Business Title', 'ar' => 'عنوان عملي']
```

### Setting Translations

```php
// Set single translation
$business->setTranslation('title', 'en', 'New English Title');
$business->setTranslation('title', 'ar', 'العنوان العربي الجديد');
$business->save();

// Set multiple translations at once
$business->setTranslations('title', [
    'en' => 'Updated Title',
    'ar' => 'العنوان المحدث'
]);
$business->save();

// Set using array assignment
$business->title = [
    'en' => 'Another Title',
    'ar' => 'عنوان آخر'
];
$business->save();
```

### API Controller Usage

```php
// In BusinessController
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'title.en' => 'required|string|max:255',
        'title.ar' => 'nullable|string|max:255',
        'description_translations.en' => 'required|string',
        'description_translations.ar' => 'nullable|string',
        'phone_number' => 'required|string',
        'category_id' => 'required|exists:categories,id',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'address_text' => 'required|string',
    ]);

    $business = Business::create($validated);

    return $this->success($business, __('messages.business.created'));
}

public function show(Business $business)
{
    // Automatically returns title in current locale
    return $this->success([
        'id' => $business->id,
        'name' => $business->name,
        'title' => $business->title, // Returns based on Accept-Language header
        'description' => $business->description_translations,
        'category' => $business->category->name_translations,
    ]);
}
```

### API Request Examples

**Create Business with Translations:**
```bash
curl -X POST https://api.example.com/v1/business/businesses \
  -H "Accept-Language: en" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "name": "coffee-shop",
    "title": {
      "en": "Best Coffee Shop",
      "ar": "أفضل مقهى"
    },
    "description_translations": {
      "en": "The best coffee in town",
      "ar": "أفضل قهوة في المدينة"
    },
    "phone_number": "+1234567890",
    "category_id": 1,
    "latitude": 40.7128,
    "longitude": -74.0060,
    "address_text": "123 Main Street"
  }'
```

**Get Business (English):**
```bash
curl -H "Accept-Language: en" \
  https://api.example.com/v1/business/businesses/1

# Response:
{
  "status": 200,
  "data": {
    "id": 1,
    "title": "Best Coffee Shop",
    "description": "The best coffee in town"
  }
}
```

**Get Business (Arabic):**
```bash
curl -H "Accept-Language: ar" \
  https://api.example.com/v1/business/businesses/1

# Response:
{
  "status": 200,
  "data": {
    "id": 1,
    "title": "أفضل مقهى",
    "description": "أفضل قهوة في المدينة"
  }
}
```

---

## Models That Should Be Translated

Based on your application structure, here are the recommended models and fields to translate:

### 1. Business ✅ (Already Implemented)
- `title` (JSON)
- `description_translations` (JSON)

### 2. Category
- `name_translations` (JSON)

### 3. Subcategory
- `name_translations` (JSON)

### 4. Amenity (if exists)
- `name_translations` (JSON)
- `description_translations` (JSON)

### 5. BusinessFaq
- `question_translations` (JSON)
- `answer_translations` (JSON)

### 6. MediaPost
- `caption_translations` (JSON)

### 7. Review
- `comment_translations` (JSON) - Optional, users write in their language

---

## Migration Templates for Other Models

### Category
```php
Schema::table('categories', function (Blueprint $table) {
    $table->json('name_translations')->nullable()->after('name');
});
DB::statement('UPDATE categories SET name_translations = JSON_OBJECT("en", name)');
```

### Subcategory
```php
Schema::table('subcategories', function (Blueprint $table) {
    $table->json('name_translations')->nullable()->after('name');
});
DB::statement('UPDATE subcategories SET name_translations = JSON_OBJECT("en", name)');
```

### Amenity
```php
Schema::table('amenities', function (Blueprint $table) {
    $table->json('name_translations')->nullable()->after('name');
    $table->json('description_translations')->nullable()->after('description');
});
DB::statement('UPDATE amenities SET name_translations = JSON_OBJECT("en", name)');
DB::statement('UPDATE amenities SET description_translations = JSON_OBJECT("en", description) WHERE description IS NOT NULL');
```

### BusinessFaq
```php
Schema::table('business_faqs', function (Blueprint $table) {
    $table->json('question_translations')->nullable()->after('question');
    $table->json('answer_translations')->nullable()->after('answer');
});
DB::statement('UPDATE business_faqs SET question_translations = JSON_OBJECT("en", question)');
DB::statement('UPDATE business_faqs SET answer_translations = JSON_OBJECT("en", answer)');
```

---

## Filament Resource Example

```php
<?php

namespace App\Filament\Resources;

use App\Models\Business;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

class BusinessResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Slug'),
                
                Forms\Components\Tabs::make('Title')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('English')
                            ->schema([
                                Forms\Components\TextInput::make('title.en')
                                    ->label('Title (English)')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Tabs\Tab::make('العربية')
                            ->schema([
                                Forms\Components\TextInput::make('title.ar')
                                    ->label('العنوان')
                                    ->maxLength(255)
                                    ->rtl(),
                            ]),
                    ]),
                
                Forms\Components\Tabs::make('Description')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('English')
                            ->schema([
                                Forms\Components\Textarea::make('description_translations.en')
                                    ->label('Description (English)')
                                    ->required()
                                    ->rows(3),
                            ]),
                        Forms\Components\Tabs\Tab::make('العربية')
                            ->schema([
                                Forms\Components\Textarea::make('description_translations.ar')
                                    ->label('الوصف')
                                    ->rows(3)
                                    ->rtl(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->getStateUsing(fn ($record) => $record->getTranslation('title', app()->getLocale()))
                    ->searchable(),
            ]);
    }
}
```

---

## Testing

```php
<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use Tests\TestCase;

class MultilingualTest extends TestCase
{
    public function test_business_has_multilingual_title()
    {
        $business = Business::factory()->create([
            'title' => [
                'en' => 'English Title',
                'ar' => 'العنوان العربي'
            ],
        ]);

        app()->setLocale('en');
        $this->assertEquals('English Title', $business->title);

        app()->setLocale('ar');
        $this->assertEquals('العنوان العربي', $business->title);
    }

    public function test_api_returns_correct_language()
    {
        $business = Business::factory()->create([
            'title' => [
                'en' => 'English Title',
                'ar' => 'العنوان العربي'
            ],
        ]);

        // English
        $response = $this->getJson("/api/v1/business/businesses/{$business->id}", [
            'Accept-Language' => 'en'
        ]);
        $response->assertJsonPath('data.title', 'English Title');

        // Arabic
        $response = $this->getJson("/api/v1/business/businesses/{$business->id}", [
            'Accept-Language' => 'ar'
        ]);
        $response->assertJsonPath('data.title', 'العنوان العربي');
    }
}
```

---

## Running the Migrations

To apply all the multilingual changes, run:

```bash
php artisan migrate
```

To rollback if needed:

```bash
php artisan migrate:rollback
```

---

## Summary

### Files Created/Modified

1. ✅ `app/Traits/HasTranslations.php` - Reusable trait
2. ✅ `app/Models/Business.php` - Updated with translations
3. ✅ `database/migrations/2026_08_28_171613_add_translatable_fields_to_businesses_table.php` - Business migrations
4. ✅ `MULTILINGUAL_TITLES_GUIDE.md` - Complete documentation
5. ✅ `MULTILINGUAL_IMPLEMENTATION.md` - API multilingual guide

### Next Steps

1. Run `php artisan migrate` to apply Business model translations
2. Create migrations for other models (Category, Subcategory, etc.)
3. Update Filament resources to include translation tabs
4. Update API controllers to handle multilingual data
5. Test with both English and Arabic requests

### Quick Command Reference

```bash
# Create migration
php artisan make:migration add_translatable_fields_to_categories_table

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Check migration status
php artisan migrate:status

# Run tests
php artisan test
```

---

## Support

For any questions or issues with the multilingual implementation, refer to:
- `MULTILINGUAL_TITLES_GUIDE.md` - Complete guide with all approaches
- `MULTILINGUAL_IMPLEMENTATION.md` - API multilingual implementation
- `app/Traits/HasTranslations.php` - Trait documentation

All translation files are located in:
- `resources/lang/en/` - English translations
- `resources/lang/ar/` - Arabic translations
