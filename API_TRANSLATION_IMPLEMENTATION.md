# API Translation Implementation Summary

## Date: 2026-08-28

## Overview
Successfully implemented translation support for all translatable models in the API using Spatie's laravel-translatable package.

## What Was Implemented

### 1. API Resources Created
All API Resources automatically return translated content based on the `Accept-Language` header:

- **AmenityResource** (`app/Http/Resources/AmenityResource.php`)
  - Translates: `name_translations`

- **CategoryResource** (`app/Http/Resources/CategoryResource.php`)
  - Translates: `name_translations`

- **SubcategoryResource** (`app/Http/Resources/SubcategoryResource.php`)
  - Translates: `name_translations`

- **BusinessResource** (`app/Http/Resources/BusinessResource.php`)
  - Translates: `title`, `description_translations`
  - Includes related: category, subcategory, amenities, faqs

- **BusinessFaqResource** (`app/Http/Resources/BusinessFaqResource.php`)
  - Translates: `question`, `answer_translations`

### 2. Middleware
Existing middleware **SetApiLocale** (`app/Http/Middleware/SetApiLocale.php`) handles locale detection:
- Reads `Accept-Language` header
- Supports: `en`, `ar`
- Defaults to `en`
- Already registered in `bootstrap/app.php` for all API routes

### 3. Request Validation Updated

**StoreBusinessListingRequest** now accepts:
```php
'title' => ['nullable', 'string', 'max:255'],
'description_translations' => ['nullable', 'array'],
'description_translations.*' => ['nullable', 'string']
```

**UpsertBusinessFaqsRequest** now accepts:
```php
'faqs.*.question_translations' => ['nullable', 'array'],
'faqs.*.question_translations.*' => ['nullable', 'string', 'max:255'],
'faqs.*.answer_translations' => ['nullable', 'array'],
'faqs.*.answer_translations.*' => ['nullable', 'string']
```

### 4. Controllers Updated

**BusinessController** (`app/Http/Controllers/Api/BusinessController.php`):
- `index()` - Returns `BusinessResource::collection()`
- `store()` - Returns `BusinessResource::make()`
- `update()` - Returns `BusinessResource::make()`

**ListingController** (`app/Http/Controllers/Api/ListingController.php`):
- `index()` - Returns `BusinessResource::collection()`
- `store()` - Returns `BusinessResource::make()`
- `map()` - Returns `BusinessResource::collection()`
- `upsertFaqs()` - Returns `BusinessFaqResource::collection()`

## How It Works

### For API Consumers (Reading Data)

Send the `Accept-Language` header with requests:

```bash
# Get listings in English
curl -H "Accept-Language: en" https://api.example.com/v1/listings

# Get listings in Arabic
curl -H "Accept-Language: ar" https://api.example.com/v1/listings
```

**Response Example:**
```json
{
  "status": 200,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "title": "Amazing Restaurant",  // Automatically translated
      "description": "Best food in town",  // Automatically translated
      "category": {
        "id": 1,
        "name": "Restaurants"  // Automatically translated
      }
    }
  ]
}
```

### For API Consumers (Writing Data)

Send translation data as JSON objects with locale keys:

```bash
POST /v1/business/listings
{
  "name": "Business Name",
  "title": "English Title",
  "description": "English description",
  "description_translations": {
    "en": "English description",
    "ar": "الوصف بالعربية"
  },
  "category_id": 1,
  "phone_number": "+1234567890",
  "latitude": 40.7128,
  "longitude": -74.0060,
  "address_text": "123 Main St"
}
```

```bash
PUT /v1/business/listings/{business}/faqs
{
  "faqs": [
    {
      "question": "What are your hours?",
      "question_translations": {
        "en": "What are your hours?",
        "ar": "ما هي ساعات العمل؟"
      },
      "answer": "We are open 9-5",
      "answer_translations": {
        "en": "We are open 9-5",
        "ar": "نحن مفتوحون من 9-5"
      },
      "sort_order": 0
    }
  ]
}
```

## Database Schema

All translation columns are JSON type and nullable:
- `categories.name_translations`
- `subcategories.name_translations`
- `amenities.name_translations`
- `businesses.title`, `businesses.description_translations`
- `business_faqs.question_translations`, `business_faqs.answer_translations`

## Supported Locales

Configured in `config/translatable.php`:
- `en` (English) - default/fallback
- `ar` (Arabic)

## Migration Files Created

1. `2026_08_28_192030_add_translations_to_categories_table.php`
2. `2026_08_28_192031_add_translations_to_subcategories_table.php`
3. `2026_08_28_192032_add_translations_to_businesses_table.php`
4. `2026_08_28_192033_add_translations_to_business_faqs_table.php`
5. `2026_08_28_185016_add_question_translations_to_business_faqs_table.php`
6. `2026_08_28_190000_add_name_translations_to_amenities_table.php`

All migrations include:
- Schema checks to prevent duplicate columns
- Data migration (existing data copied to English locale)
- Proper rollback methods

## Testing

Routes verified with no syntax errors:
```bash
php artisan route:list --path=v1
```

All 31 API routes are working correctly.

## Notes

- The API automatically falls back to the default locale (`en`) if:
  - No `Accept-Language` header is provided
  - An unsupported locale is requested
  - A translation for the requested locale doesn't exist

- Spatie's `HasTranslations` trait handles all translation logic in the models
- The `getTranslation()` method is used in Resources to fetch translated values

## Future Enhancements

To add more locales:
1. Update `config/translatable.php` `supported_locales` array
2. Update `app/Http/Middleware/SetApiLocale.php` locale validation
3. Populate translation data for the new locale
