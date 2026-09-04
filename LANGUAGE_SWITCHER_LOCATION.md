# Language Switcher Location & Setup

## ✅ Language Switcher is Now Active!

### Where to Find It

The language switcher appears in the **top-right corner** of every page in the Filament admin panel:

**Location:** `/admin` (any page in the admin panel)

**Visual appearance:**
- 🌐 Globe icon
- Current language name ("English" or "العربية")
- Dropdown arrow (⌄)

### How to Use It

1. **Access the admin panel:**
   ```
   Navigate to: http://localhost:8000/admin
   ```

2. **Look at the top navigation bar** (topbar) on the right side

3. **Click the language button** - You'll see:
   - 🌐 Globe icon
   - "English" or "العربية"
   - Dropdown arrow

4. **Select your language:**
   - Click to open dropdown
   - Choose "English" or "العربية"
   - Page automatically refreshes with selected language

5. **All content updates automatically:**
   - Table columns show translated names
   - Category names
   - Subcategory names
   - Amenity names  
   - Business names and descriptions
   - FAQ questions and answers

### Technical Implementation

**Render Hook:**
```php
// In AdminPanelProvider.php
->renderHook(
    'panels::topbar.end',
    fn (): string => view('filament.components.language-switcher')->render(),
)
```

**Language Switcher Component:**
- File: `resources/views/filament/components/language-switcher.blade.php`
- Uses Alpine.js for dropdown functionality
- Form POST to switch language endpoint
- Session-based locale storage

**Route:**
```php
POST /admin/switch-locale
```

**Middleware:**
- `SetFilamentLocale` - Sets app locale from session on every request
- Registered in AdminPanelProvider middleware stack

### Supported Languages

- **English** (`en`) - Default
- **Arabic** (`ar`) - العربية

### Session Storage

Language preference is stored in session:
```php
session(['filament_locale' => 'ar']);
```

Persists across:
- Page navigation
- Resource views
- Form submissions
- Table listings

### What Gets Translated

**Filament Dashboard:**
- ✅ Category names
- ✅ Subcategory names
- ✅ Amenity names
- ✅ Business names (via `name_translations`)
- ✅ Business descriptions
- ✅ FAQ questions
- ✅ FAQ answers

**API Responses:**
- ✅ All translatable fields automatically return content based on `Accept-Language` header

### Troubleshooting

**Issue: Can't see the language switcher**

1. **Clear all caches:**
   ```bash
   php artisan route:clear
   php artisan view:clear
   php artisan cache:clear
   php artisan filament:cache-components
   ```

2. **Check AdminPanelProvider:**
   - Verify `renderHook` is registered
   - File: `app/Providers/Filament/AdminPanelProvider.php`

3. **Verify view exists:**
   - File: `resources/views/filament/components/language-switcher.blade.php`

4. **Check route exists:**
   ```bash
   php artisan route:list --path=switch-locale
   ```
   Should show: `POST admin/switch-locale`

5. **Hard refresh browser:**
   - Press `Ctrl + Shift + R` (Windows/Linux)
   - Press `Cmd + Shift + R` (Mac)

**Issue: Language doesn't change after clicking**

1. Check browser console for errors
2. Verify session driver is working (`config/session.php`)
3. Clear browser cache and cookies for localhost

**Issue: Translations not showing**

1. Verify data exists in translation columns:
   ```bash
   php artisan tinker
   >>> Category::first()->name_translations
   ```

2. Check JSON format:
   ```json
   {"en": "English Name", "ar": "اسم عربي"}
   ```

3. Verify model has `$translatable` array configured

### Browser Compatibility

The language switcher uses:
- Alpine.js (included with Filament)
- Standard HTML forms
- No JavaScript frameworks required

Works in all modern browsers:
- ✅ Chrome
- ✅ Firefox
- ✅ Safari
- ✅ Edge

---

## Quick Reference

| Action | Location |
|--------|----------|
| Switch language | Top-right corner, click globe icon 🌐 |
| Add translations | Edit any resource → "Translations" section |
| API language | Send `Accept-Language: ar` header |
| Check current locale | Dashboard shows current language in button |
| Session storage | `session('filament_locale')` |
| Supported codes | `en`, `ar` |

---

## Example Usage

### Adding Translations in Dashboard

1. Go to **Categories** → Create/Edit
2. Fill in `name` field: "Restaurants"
3. Scroll down to **"Translations"** section
4. Add translations:
   - Key: `en` → Value: "Restaurants"
   - Key: `ar` → Value: "مطاعم"
5. Save

6. Switch language to Arabic (top-right 🌐 button)
7. Category now shows as "مطاعم"

### Viewing Translated Content

**English view:**
- Click language switcher
- Select "English"
- All names display in English

**Arabic view:**
- Click language switcher  
- Select "العربية"
- All names display in Arabic
- If translation missing → shows original English name

---

## Summary

✅ Language switcher is now visible in the top-right corner of all Filament admin pages  
✅ Uses Filament's render hook system (`panels::topbar.end`)  
✅ Simple dropdown with form POST to switch languages  
✅ Session-based language persistence  
✅ Automatic content translation in all tables and views  
✅ Supports English and Arabic with easy extension for more languages

The language switcher should now be visible when you access `/admin`!
