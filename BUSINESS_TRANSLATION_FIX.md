# Business Translation Fix - Summary

## Date: 2026-08-28

## Changes Made

### 1. Database Schema Fixed
**Removed unnecessary columns:**
- ❌ `title` column (was nullable text)
- ❌ `description_translations` column (was nullable JSON)

**Added proper translation column:**
- ✅ `name_translations` (JSON, nullable) - for translating business names

**Kept original columns:**
- ✅ `name` (varchar) - primary business name
- ✅ `description` (text) - primary business description (translatable via Spatie)

### 2. Business Model Updated
**File:** `app/Models/Business.php`

**Translatable fields:**
```php
public $translatable = ['name_translations', 'description'];
```

**Fillable fields updated:**
```php
protected $fillable = [
    'user_id', 'name', 'slug', 'description', 'name_translations',
    'phone_number', 'whatsapp_number', 'category_id', 'subcategory_id',
    'latitude', 'longitude', 'address_text', 'expires_at', 'status',
    'approval_status', 'approved_at', 'rejection_reason', 'average_rating'
];
```

### 3. Filament Dashboard Updated
**File:** `app/Filament/Resources/BusinessResource.php`

**Form fields:**
- `name` - TextInput (required)
- `description` - Textarea (optional)
- Translation section with `name_translations` KeyValue field

**Table columns:**
- `name` - Shows translated name based on selected locale
- `description` - Shows translated description (hidden by default, 50 char limit)

### 4. API Resources Updated
**File:** `app/Http/Resources/BusinessResource.php`

**Returns:**
```php
'name' => $this->getTranslation('name_translations', app()->getLocale()),
'description' => $this->getTranslation('description', app()->getLocale()),
```

**File:** `app/Http/Requests/StoreBusinessListingRequest.php`

**Validation rules:**
```php
'name' => ['required', 'string', 'max:255'],
'name_translations' => ['nullable', 'array'],
'name_translations.*' => ['nullable', 'string', 'max:255'],
'description' => ['nullable', 'string'],
```

---

## How to Use

### In Filament Dashboard

1. **Create/Edit Business:**
   - Fill in `name` field (e.g., "Amazing Restaurant")
   - Fill in `description` field (e.g., "Best food in town")
   - Expand "Translations" section
   - Add translations:
     - Key: `en`, Value: "Amazing Restaurant"
     - Key: `ar`, Value: "مطعم رائع"

2. **View Businesses:**
   - Switch language using dropdown (top-right corner with globe icon 🌐)
   - Business names automatically display in selected language
   - If translation missing, shows original `name` value

### In API

**Creating a business with translations:**
```json
POST /api/v1/business/listings
{
  "name": "Amazing Restaurant",
  "name_translations": {
    "en": "Amazing Restaurant",
    "ar": "مطعم رائع"
  },
  "description": "Best food in town with authentic flavors",
  "phone_number": "+1234567890",
  "category_id": 1,
  "latitude": 40.7128,
  "longitude": -74.0060,
  "address_text": "123 Main St"
}
```

**Getting businesses (returns translated names based on Accept-Language header):**
```bash
# English
curl -H "Accept-Language: en" /api/v1/listings

# Arabic
curl -H "Accept-Language: ar" /api/v1/listings
```

---

## Translation Structure

### Business Translation Fields

| Field | Type | Translation Column | Storage |
|-------|------|-------------------|---------|
| `name` | varchar | `name_translations` | JSON: `{"en": "Name", "ar": "اسم"}` |
| `description` | text | (same column) | JSON: `{"en": "Desc", "ar": "وصف"}` |

### How Spatie Handles It

**For `name_translations` (separate JSON column):**
```php
// Set translation
$business->setTranslation('name_translations', 'ar', 'مطعم رائع');

// Get translation
$business->getTranslation('name_translations', 'ar'); // Returns: "مطعم رائع"
```

**For `description` (translates in same column):**
```php
// Set translation
$business->setTranslation('description', 'ar', 'أفضل طعام في المدينة');

// Get translation
$business->getTranslation('description', 'ar'); // Returns: "أفضل طعام في المدينة"
```

---

## Migration Applied

**File:** `database/migrations/2026_08_28_202310_fix_business_translations.php`

- Dropped `title` column
- Dropped `description_translations` column
- Added `name_translations` JSON column
- Migrated existing `name` data to `name_translations` with `en` locale

---

## Language Switcher Location

The language switcher is located in the **dashboard header** (top-right corner):

1. Navigate to `/admin` (Filament dashboard)
2. Look at the top-right area of the page
3. You'll see a button with:
   - 🌐 Globe icon
   - Current language text ("English" or "العربية")
   - Dropdown arrow

Click it to switch between English and Arabic. The page will refresh and all translatable content will update.

---

## Summary

✅ Business model now has only `name` and `description` as translatable fields  
✅ Removed unnecessary `title` and `description_translations` columns  
✅ Added proper `name_translations` JSON column  
✅ Updated Filament forms to show correct translation fields  
✅ Updated API resources to return correct translated fields  
✅ Language switcher is visible in dashboard header  
✅ All translations working correctly in both dashboard and API
