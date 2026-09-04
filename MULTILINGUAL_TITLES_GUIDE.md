# Multi-Language Titles Implementation Guide

This guide provides three approaches to implement multi-language titles in your Laravel application.

## Approach 1: JSON Column (Recommended for Simple Use Cases)

### Step 1: Add Migration

Create a migration to add JSON columns for translatable fields:

```bash
php artisan make:migration add_translatable_fields_to_businesses_table
```

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
        Schema::table('businesses', function (Blueprint $table) {
            $table->json('title')->nullable()->after('name');
            $table->json('description_translations')->nullable()->after('description');
        });

        // Migrate existing data
        DB::statement('UPDATE businesses SET title = JSON_OBJECT("en", name) WHERE name IS NOT NULL');
        DB::statement('UPDATE businesses SET description_translations = JSON_OBJECT("en", description) WHERE description IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['title', 'description_translations']);
        });
    }
};
```

### Step 2: Update Model

Add the `HasTranslations` trait to your model:

```php
<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasTranslations;

    protected $translatable = ['title', 'description_translations'];

    protected $fillable = [
        'name',
        'title',
        'description',
        'description_translations',
        // ... other fields
    ];

    protected $casts = [
        'title' => 'array',
        'description_translations' => 'array',
    ];
}
```

### Step 3: Usage Examples

```php
// Create with translations
$business = Business::create([
    'name' => 'slug-name', // for URL slug
    'title' => [
        'en' => 'My Business Title',
        'ar' => 'عنوان عملي'
    ],
    'description_translations' => [
        'en' => 'English description',
        'ar' => 'الوصف العربي'
    ]
]);

// Get title in current locale
$title = $business->title; // Returns based on app()->getLocale()

// Get title in specific locale
$titleEn = $business->getTranslation('title', 'en');
$titleAr = $business->getTranslation('title', 'ar');

// Set translations
$business->setTranslation('title', 'en', 'New English Title');
$business->setTranslation('title', 'ar', 'العنوان العربي الجديد');

// Set multiple translations at once
$business->setTranslations('title', [
    'en' => 'Updated Title',
    'ar' => 'العنوان المحدث'
]);

// Get all translations
$allTitles = $business->getTranslations('title');
// Returns: ['en' => 'Updated Title', 'ar' => 'العنوان المحدث']
```

### Step 4: API Controller Usage

```php
// In your controller
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'title.en' => 'required|string|max:255',
        'title.ar' => 'nullable|string|max:255',
        'description_translations.en' => 'required|string',
        'description_translations.ar' => 'nullable|string',
    ]);

    $business = Business::create($validated);

    return $this->success($business, __('messages.business.created'));
}

public function show(Business $business)
{
    // Automatically returns title in current locale
    return response()->json([
        'id' => $business->id,
        'name' => $business->name,
        'title' => $business->title,
        'description' => $business->description_translations,
        // To include all languages:
        'all_titles' => $business->getTranslations('title'),
    ]);
}
```

### Step 5: Filament Resource Configuration

```php
// In BusinessResource.php
public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            
            Forms\Components\Tabs::make('Title')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('English')
                        ->schema([
                            Forms\Components\TextInput::make('title.en')
                                ->label('Title (English)')
                                ->required()
                                ->maxLength(255),
                        ]),
                    Forms\Components\Tabs\Tab::make('Arabic')
                        ->schema([
                            Forms\Components\TextInput::make('title.ar')
                                ->label('العنوان')
                                ->maxLength(255),
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
                    Forms\Components\Tabs\Tab::make('Arabic')
                        ->schema([
                            Forms\Components\Textarea::make('description_translations.ar')
                                ->label('الوصف')
                                ->rows(3),
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
                ->getStateUsing(fn ($record) => $record->getTranslation('title', app()->getLocale()))
                ->searchable(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable(),
        ]);
}
```

---

## Approach 2: Dedicated Translations Table (Better for Complex Systems)

### Step 1: Create Translations Table

```bash
php artisan make:migration create_translations_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('translatable_type'); // Model class
            $table->unsignedBigInteger('translatable_id'); // Model ID
            $table->string('locale', 10); // 'en', 'ar', etc.
            $table->string('key'); // 'title', 'description', etc.
            $table->text('value');
            $table->timestamps();

            $table->index(['translatable_type', 'translatable_id']);
            $table->unique(['translatable_type', 'translatable_id', 'locale', 'key'], 'translations_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
```

### Step 2: Create Translation Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = [
        'locale',
        'key',
        'value',
    ];

    public function translatable()
    {
        return $this->morphTo();
    }
}
```

### Step 3: Update Business Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = ['name', 'slug', 'phone_number', 'category_id', /* ... */];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function translate(string $key, ?string $locale = null, $default = null)
    {
        $locale = $locale ?? app()->getLocale();
        
        return $this->translations()
            ->where('locale', $locale)
            ->where('key', $key)
            ->value('value') ?? $default ?? $this->{$key};
    }

    public function setTranslation(string $key, string $locale, string $value): void
    {
        $this->translations()->updateOrCreate(
            ['locale' => $locale, 'key' => $key],
            ['value' => $value]
        );
    }

    // Accessor for title
    public function titleAttribute(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => $this->translate('title')
        );
    }

    // Accessor for description
    public function descriptionAttribute(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => $this->translate('description')
        );
    }
}
```

### Step 4: Usage Examples

```php
// Create business with translations
$business = Business::create([
    'name' => 'my-business',
    'slug' => 'my-business',
]);

$business->setTranslation('title', 'en', 'My Business Title');
$business->setTranslation('title', 'ar', 'عنوان عملي');
$business->setTranslation('description', 'en', 'English description');
$business->setTranslation('description', 'ar', 'الوصف العربي');

// Get translation
$title = $business->translate('title'); // Uses current locale
$titleAr = $business->translate('title', 'ar'); // Specific locale
```

---

## Approach 3: Using spatie/laravel-translatable Package

### Step 1: Install Package

```bash
composer require spatie/laravel-translatable
```

### Step 2: Publish Config

```bash
php artisan vendor:publish --provider="Spatie\Translatable\TranslatableServiceProvider"
```

### Step 3: Configure Model

```php
<?php

namespace App\Models;

use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'description'];

    protected $fillable = ['name', 'title', 'description', 'slug'];

    // Optional: Convert JSON to array automatically
    protected $casts = [
        'title' => 'array',
        'description' => 'array',
    ];
}
```

### Step 4: Usage Examples

```php
// Create with translations
$business = Business::create([
    'name' => 'slug-name',
    'title' => [
        'en' => 'My Business Title',
        'ar' => 'عنوان عملي'
    ],
    'description' => [
        'en' => 'English description',
        'ar' => 'الوصف العربي'
    ]
]);

// Get translation (automatically uses current locale)
echo $business->title; // "My Business Title" (if locale is 'en')

// Get translation in specific locale
echo $business->getTranslation('title', 'ar'); // "عنوان عملي"

// Set translation
$business->setTranslation('title', 'en', 'New English Title');

// Get all translations
$allTitles = $business->getTranslations('title');

// Remove a translation
$business->forgetTranslation('title', 'ar');

// Check if translation exists
$hasArabic = $business->hasTranslation('title', 'ar');
```

### Step 5: Validation Rules

```php
// In your FormRequest or controller
public function rules()
{
    return [
        'title.en' => 'required|string|max:255',
        'title.ar' => 'nullable|string|max:255',
        'description.en' => 'required|string',
        'description.ar' => 'nullable|string',
    ];
}
```

---

## Comparison of Approaches

| Feature | JSON Column | Translations Table | spatie/laravel-translatable |
|---------|-------------|-------------------|----------------------------|
| **Setup Complexity** | Low | Medium | Low (package) |
| **Query Performance** | Fast | Slower (joins) | Fast |
| **Search by Translation** | Difficult | Easy | Moderate |
| **Database Size** | Compact | Larger | Compact |
| **Flexibility** | Moderate | High | Moderate |
| **Maintenance** | Easy | Moderate | Easy |
| **Best For** | Simple apps | Complex systems | Most apps |

---

## Recommendations

### Use JSON Column (Approach 1) when:
- You have 2-3 languages
- Simple translation requirements
- Need fast queries
- Want to keep database simple

### Use Translations Table (Approach 2) when:
- You need to search by translated content
- Complex translation logic
- Many languages (>5)
- Need translation history/auditing

### Use spatie/laravel-translatable (Approach 3) when:
- Want a ready-made solution
- Need package support
- Want to avoid custom implementation
- Prefer battle-tested code

---

## Filament Resource Example (Complete)

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessResource\Pages;
use App\Models\Business;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label(__('filament.resources.business.fields.name')),
                        
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Translations')
                    ->schema([
                        Forms\Components\Tabs::make('Translations')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('English')
                                    ->schema([
                                        Forms\Components\TextInput::make('title.en')
                                            ->label('Title (English)')
                                            ->required()
                                            ->maxLength(255),
                                        
                                        Forms\Components\Textarea::make('description.en')
                                            ->label('Description (English)')
                                            ->required()
                                            ->rows(3),
                                    ]),
                                
                                Forms\Components\Tabs\Tab::make('العربية')
                                    ->schema([
                                        Forms\Components\TextInput::make('title.ar')
                                            ->label('العنوان')
                                            ->maxLength(255)
                                            ->rtl(),
                                        
                                        Forms\Components\Textarea::make('description.ar')
                                            ->label('الوصف')
                                            ->rows(3)
                                            ->rtl(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->getStateUsing(function ($record) {
                        return $record->getTranslation('title', app()->getLocale());
                    })
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Add filters here
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Add relation managers
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinesses::route('/'),
            'create' => Pages\CreateBusiness::route('/create'),
            'edit' => Pages\EditBusiness::route('/{record}/edit'),
        ];
    }
}
```

---

## Testing Multilingual Titles

```php
<?php

namespace Tests\Feature;

use App\Models\Business;
use Tests\TestCase;

class MultilingualTitleTest extends TestCase
{
    public function test_business_can_have_multilingual_title()
    {
        $business = Business::create([
            'name' => 'test-business',
            'title' => [
                'en' => 'English Title',
                'ar' => 'العنوان العربي'
            ],
        ]);

        // Test English
        app()->setLocale('en');
        $this->assertEquals('English Title', $business->title);

        // Test Arabic
        app()->setLocale('ar');
        $this->assertEquals('العنوان العربي', $business->title);

        // Test getTranslation method
        $this->assertEquals('English Title', $business->getTranslation('title', 'en'));
        $this->assertEquals('العنوان العربي', $business->getTranslation('title', 'ar'));
    }

    public function test_api_returns_correct_language()
    {
        $business = Business::factory()->create([
            'title' => [
                'en' => 'English Title',
                'ar' => 'العنوان العربي'
            ],
        ]);

        // Test English response
        $response = $this->getJson("/api/v1/business/businesses/{$business->id}", [
            'Accept-Language' => 'en'
        ]);
        $response->assertJsonPath('data.title', 'English Title');

        // Test Arabic response
        $response = $this->getJson("/api/v1/business/businesses/{$business->id}", [
            'Accept-Language' => 'ar'
        ]);
        $response->assertJsonPath('data.title', 'العنوان العربي');
    }
}
```

---

## Summary

Choose the approach that best fits your needs:

1. **JSON Column**: Simple, fast, good for 2-3 languages
2. **Translations Table**: Complex, searchable, good for many languages
3. **spatie/laravel-translatable**: Ready-made solution, easy to implement

For your current application with Arabic and English support, I recommend **Approach 1 (JSON Column)** or **Approach 3 (spatie package)**.
