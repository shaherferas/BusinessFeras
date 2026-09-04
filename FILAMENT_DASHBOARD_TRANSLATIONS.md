# Filament Dashboard Translation Implementation

## Date: 2026-08-28

## Overview
Successfully implemented language selection and translation support in the Filament admin dashboard. Users can now switch between English and Arabic, and all translatable fields have dedicated input fields in forms.

---

## 1. Language Switcher

### Components Created

**Middleware: `SetFilamentLocale`** (`app/Http/Middleware/SetFilamentLocale.php`)
- Automatically sets the application locale based on the session value
- Registered in AdminPanelProvider middleware stack
- Validates locale against supported locales (en, ar)

**Custom Dashboard Page** (`app/Filament/Pages/Dashboard.php`)
- Extends base Filament Dashboard
- Includes language switching functionality
- Updates session when locale changes
- Displays language dropdown in header

**Dashboard View** (`resources/views/filament/pages/dashboard.blade.php`)
- Custom dashboard layout with language switcher in header
- Dropdown showing current language with flag icon
- Options for English and Arabic (العربية)
- Automatically refreshes page after language change

### How It Works

1. User clicks language dropdown in dashboard header
2. Selects desired language (English or Arabic)
3. Session is updated with `filament_locale` key
4. Page refreshes with new locale applied
5. All table columns and form labels update to show translated content

---

## 2. Translation Fields in Forms

All translatable resources now include a **"Translations"** section with KeyValue fields for multilingual content.

### Updated Resources

#### **CategoryResource** (`app/Filament/Resources/CategoryResource.php`)
**Translation Fields:**
- `name_translations` - Category name in different languages

**Form Structure:**
```php
Forms\Components\Section::make('Translations')
    ->schema([
        Forms\Components\KeyValue::make('name_translations')
            ->keyLabel('Language Code')
            ->valueLabel('Translated Name')
            ->default(['en' => '', 'ar' => ''])
    ])
```

#### **SubcategoryResource** (`app/Filament/Resources/SubcategoryResource.php`)
**Translation Fields:**
- `name_translations` - Subcategory name in different languages

#### **AmenityResource** (`app/Filament/Resources/AmenityResource.php`)
**Translation Fields:**
- `name_translations` - Amenity name in different languages

#### **BusinessResource** (`app/Filament/Resources/BusinessResource.php`)
**Translation Fields:**
- `title` - Business display title (stored directly in `title` column)
- `description_translations` - Business description in different languages

**Form Structure:**
```php
Forms\Components\Section::make('Business Information')
    ->schema([
        Forms\Components\TextInput::make('title'),
        Forms\Components\Textarea::make('description'),
    ]),

Forms\Components\Section::make('Translations')
    ->schema([
        Forms\Components\KeyValue::make('description_translations')
            ->keyLabel('Language Code')
            ->valueLabel('Translated Description')
            ->default(['en' => '', 'ar' => ''])
    ])
```

#### **FAQs Relation Manager** (`app/Filament/Resources/BusinessResource/RelationManagers/FaqsRelationManager.php`)
**Translation Fields:**
- `question_translations` - FAQ question in different languages
- `answer_translations` - FAQ answer in different languages

**Form Structure:**
```php
Forms\Components\Section::make('Translations')
    ->schema([
        Forms\Components\KeyValue::make('question_translations'),
        Forms\Components\KeyValue::make('answer_translations'),
    ])
```

### Field Configuration

All KeyValue translation fields are configured with:
- **keyLabel**: "Language Code" 
- **valueLabel**: "Translated [Field Name]"
- **default**: `['en' => '', 'ar' => '']` - Pre-populated with supported locales
- **addActionLabel**: "Add Translation"
- **reorderable**: `false` - Maintains language order
- **helperText**: Instructions for using language codes

---

## 3. Table Columns with Translations

All table listings now automatically display translated content based on the selected dashboard language.

### Implementation Pattern

```php
Tables\Columns\TextColumn::make('name')
    ->formatStateUsing(function ($record) {
        $locale = app()->getLocale();
        return $record->getTranslation('name_translations', $locale) ?: $record->name;
    })
```

### Updated Tables

#### **CategoryResource**
- `name` column - Shows translated category name

#### **SubcategoryResource**
- `name` column - Shows translated subcategory name

#### **AmenityResource**
- `name` column - Shows translated amenity name

#### **BusinessResource**
- `title` column - Shows translated business title
- `category.name` column - Shows translated category name

#### **FAQs Relation Manager**
- `question` column - Shows translated FAQ question
- `answer` column - Shows translated FAQ answer (limited to 40 characters)

### Fallback Behavior

If a translation doesn't exist for the selected locale, the original field value is displayed:
```php
return $record->getTranslation('name_translations', $locale) ?: $record->name;
```

---

## 4. User Experience

### Language Selection Flow

1. **Dashboard Access**
   - User logs into Filament admin panel at `/admin`
   - Dashboard loads with default language (English)
   - Language dropdown visible in top-right header

2. **Switching Languages**
   - Click language dropdown
   - See current language highlighted with checkmark
   - Select new language (English or Arabic)
   - Page automatically refreshes
   - All content updates to selected language

3. **Creating/Editing Records**
   - Open any translatable resource (Categories, Businesses, Amenities, etc.)
   - Fill in primary fields (name, description, etc.)
   - Expand "Translations" section
   - Add translations for each supported language
   - Use language codes: `en` for English, `ar` for Arabic

4. **Viewing Lists**
   - Table columns automatically show content in selected language
   - If translation missing, falls back to original content
   - No manual switching needed

### Visual Indicators

**Language Dropdown Button:**
- Globe icon (🌐)
- Current language name
- Dropdown arrow

**Translation Section:**
- Collapsible section in forms
- Key-value pairs for easy language code input
- Helper text with usage instructions
- Pre-populated with `en` and `ar` keys

---

## 5. Technical Details

### Session Storage

Language preference is stored in session:
```php
session(['filament_locale' => 'ar']);
```

### Locale Application

Middleware sets app locale on every request:
```php
app()->setLocale($locale);
```

### Supported Locales

Configured in `config/translatable.php`:
```php
'supported_locales' => ['en', 'ar'],
'fallback_locale' => 'en',
```

### Translation Storage

Translations are stored as JSON in database columns:
```json
{
  "en": "Category Name",
  "ar": "اسم الفئة"
}
```

### Spatie Package Integration

All models use `Spatie\Translatable\HasTranslations` trait:
```php
public $translatable = ['name_translations'];
```

Filament forms and tables use the `getTranslation()` method:
```php
$record->getTranslation('name_translations', 'ar')
```

---

## 6. Configuration Files

### AdminPanelProvider Updated
```php
->middleware([
    // ... other middleware
    \App\Http\Middleware\SetFilamentLocale::class,
])
```

### Custom Dashboard Registered
Dashboard now uses custom page at `app/Filament/Pages/Dashboard.php` instead of default Filament dashboard.

---

## 7. Files Modified/Created

### Created Files:
1. `app/Http/Middleware/SetFilamentLocale.php`
2. `app/Filament/Pages/Dashboard.php`
3. `resources/views/filament/pages/dashboard.blade.php`
4. `app/Filament/Pages/LanguageSwitcher.php` (component)
5. `resources/views/filament/components/language-switcher.blade.php` (component)

### Modified Files:
1. `app/Providers/Filament/AdminPanelProvider.php`
2. `app/Filament/Resources/CategoryResource.php`
3. `app/Filament/Resources/SubcategoryResource.php`
4. `app/Filament/Resources/AmenityResource.php`
5. `app/Filament/Resources/BusinessResource.php`
6. `app/Filament/Resources/BusinessResource/RelationManagers/FaqsRelationManager.php`

---

## 8. Testing the Implementation

### Step-by-Step Test

1. **Access Dashboard**
   ```
   Navigate to: http://localhost:8000/admin
   ```

2. **Change Language**
   - Click language dropdown (top-right)
   - Select "العربية" (Arabic)
   - Verify page refreshes

3. **Create Category with Translations**
   - Go to Categories → Create
   - Fill in name: "Restaurants"
   - Expand "Translations" section
   - Add translation: `en` → "Restaurants", `ar` → "مطاعم"
   - Save

4. **View in Different Languages**
   - Switch to English → See "Restaurants"
   - Switch to Arabic → See "مطاعم"

5. **Create Business with Translations**
   - Go to Businesses → Create
   - Fill in name, title, description
   - Expand "Translations" section
   - Add description translations
   - Save

6. **Create FAQs with Translations**
   - Edit a business
   - Go to FAQs tab
   - Create FAQ with question and answer
   - Expand "Translations" section
   - Add question_translations and answer_translations
   - Save

---

## 9. Future Enhancements

### Potential Improvements:

1. **RTL Support for Arabic**
   - Add RTL layout when Arabic is selected
   - Update Filament theme configuration

2. **More Languages**
   - Add French, Spanish, German, etc.
   - Update `config/translatable.php`
   - Update language dropdown

3. **Navigation Translation**
   - Translate Filament navigation menu items
   - Translate resource labels

4. **Validation Messages**
   - Translate error messages
   - Create language files in `lang/`

5. **Rich Text Editor for Translations**
   - Replace KeyValue with RichEditor for long text
   - Better formatting support

---

## 10. Troubleshooting

### Common Issues:

**Issue: Language doesn't change after clicking dropdown**
- Clear browser cache
- Clear Laravel cache: `php artisan cache:clear`
- Check session driver is working

**Issue: Translations not showing in tables**
- Verify data exists in `name_translations` column
- Check JSON format: `{"en": "value", "ar": "قيمة"}`
- Verify locale is set correctly: `dd(app()->getLocale())`

**Issue: KeyValue field not saving**
- Check model's `$fillable` array includes translation field
- Verify column type is JSON in migration
- Check model has `$translatable` array configured

**Issue: Dashboard not loading**
- Clear view cache: `php artisan view:clear`
- Check Dashboard.php extends correct base class
- Verify custom view file exists

---

## Summary

The Filament dashboard now has full translation support with:
- ✅ Language switcher in dashboard header
- ✅ Translation input fields in all forms
- ✅ Automatic translation display in tables
- ✅ Session-based language persistence
- ✅ Fallback to original content if translation missing
- ✅ Support for English and Arabic
- ✅ Easy to extend with more languages

All translatable resources (Categories, Subcategories, Amenities, Businesses, FAQs) now support multilingual content in both the API and admin dashboard.
