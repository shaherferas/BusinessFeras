# Multi-Language Support Implementation (Arabic & English)

This document outlines the complete implementation of multi-language support for both APIs and Dashboard.

## Overview

The application now supports Arabic (ar) and English (en) languages with:
- API responses in the requested language
- Dashboard RTL support for Arabic
- Comprehensive translation files
- Automatic language detection from Accept-Language header

## Implementation Details

### 1. Language Files Structure

```
resources/lang/
├── ar/
│   ├── messages.php       # API messages in Arabic
│   ├── validation.php     # Validation messages in Arabic
│   └── filament.php       # Dashboard translations in Arabic
└── en/
    ├── messages.php       # API messages in English
    ├── validation.php     # Validation messages in English
    └── filament.php       # Dashboard translations in English
```

### 2. Middleware Configuration

#### API Locale Middleware (`app/Http/Middleware/SetApiLocale.php`)
- Automatically detects language from `Accept-Language` header
- Supports: `ar` (Arabic), `en` (English)
- Falls back to English if unsupported language is requested
- Applied to all API routes

**Usage:**
```bash
# English request
curl -H "Accept-Language: en" https://api.example.com/v1/auth/login

# Arabic request
curl -H "Accept-Language: ar" https://api.example.com/v1/auth/login
```

### 3. API Translation Implementation

All API responses use the `__()` helper function to return translated messages:

**Example:**
```php
// Before
return $this->success($data, 'OTP sent via email');

// After
return $this->success($data, __('messages.auth.otp_sent'));
```

**Available Message Categories:**
- `messages.auth.*` - Authentication messages
- `messages.business.*` - Business-related messages
- `messages.reviews.*` - Review messages
- `messages.media.*` - Media upload messages
- `messages.chat.*` - Chat/messaging
- `messages.general.*` - General messages
- `messages.validation.*` - Validation errors

### 4. Dashboard (Filament) Configuration

The Filament admin panel has been configured with:
- Language detection
- RTL support for Arabic
- SPA mode for better performance

**Features:**
- Users can switch between languages in the dashboard
- Arabic interface automatically uses RTL layout
- All navigation, forms, and tables are translated

### 5. Translation Keys Reference

#### Authentication Messages
```php
__('messages.auth.otp_sent')              // "OTP sent via email" / "تم إرسال رمز التحقق عبر البريد الإلكتروني"
__('messages.auth.invalid_credentials')   // "Email or password is incorrect" / "البريد الإلكتروني أو كلمة المرور غير صحيحة"
__('messages.auth.logged_out')            // "Logged out successfully" / "تم تسجيل الخروج بنجاح"
```

#### Business Messages
```php
__('messages.business.created')           // "Business created successfully" / "تم إنشاء العمل بنجاح"
__('messages.business.updated')           // "Business updated successfully" / "تم تحديث العمل بنجاح"
__('messages.business.not_found')         // "Business not found" / "العمل غير موجود"
```

#### Validation Messages
Laravel's built-in validation automatically uses the language files in `resources/lang/{locale}/validation.php`.

### 6. Testing with Different Languages

**API Testing:**

English:
```bash
curl -X POST https://api.example.com/v1/auth/register \
  -H "Accept-Language: en" \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com","password":"Secret123","password_confirmation":"Secret123"}'
```

Arabic:
```bash
curl -X POST https://api.example.com/v1/auth/register \
  -H "Accept-Language: ar" \
  -H "Content-Type: application/json" \
  -d '{"name":"أحمد","email":"ahmed@example.com","password":"Secret123","password_confirmation":"Secret123"}'
```

**Response Examples:**

English Response:
```json
{
  "status": 201,
  "message": "OTP sent via email",
  "data": {
    "email": "john@example.com"
  }
}
```

Arabic Response:
```json
{
  "status": 201,
  "message": "تم إرسال رمز التحقق عبر البريد الإلكتروني",
  "data": {
    "email": "ahmed@example.com"
  }
}
```

### 7. Configuration Files

**config/app.php:**
```php
'locale' => env('APP_LOCALE', 'en'),
'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
```

**.env:**
```env
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
```

### 8. Adding New Translations

To add new translation keys:

1. **For API messages**, edit:
   - `resources/lang/en/messages.php`
   - `resources/lang/ar/messages.php`

2. **For Dashboard**, edit:
   - `resources/lang/en/filament.php`
   - `resources/lang/ar/filament.php`

3. **For Validation**, edit:
   - `resources/lang/en/validation.php`
   - `resources/lang/ar/validation.php`

**Example:**
```php
// resources/lang/en/messages.php
'payment' => [
    'success' => 'Payment completed successfully',
    'failed' => 'Payment failed',
],

// resources/lang/ar/messages.php
'payment' => [
    'success' => 'تمت عملية الدفع بنجاح',
    'failed' => 'فشلت عملية الدفع',
],
```

**Usage in code:**
```php
return $this->success($data, __('messages.payment.success'));
```

### 9. RTL Support

The application automatically handles RTL (Right-to-Left) layout for Arabic:
- The `SetRtlDirection` middleware adds the `X-Direction: rtl` header for Arabic requests
- Filament dashboard automatically switches to RTL when Arabic is selected
- Custom CSS for RTL can be added if needed

### 10. Best Practices

1. **Always use translation keys** instead of hardcoded strings
2. **Keep translations consistent** across all languages
3. **Test both languages** when adding new features
4. **Use proper locale codes**: `en` for English, `ar` for Arabic
5. **Validate user input** in both languages
6. **Consider cultural context** when translating (not just literal translation)

### 11. Extending to More Languages

To add a new language (e.g., French):

1. Create directory: `resources/lang/fr/`
2. Copy and translate files from `en/` to `fr/`
3. Update middleware to support `fr`:
```php
if (in_array($locale, ['ar', 'en', 'fr'], true)) {
    App::setLocale($locale);
}
```

### 12. Troubleshooting

**Issue: Translations not showing**
- Clear cache: `php artisan cache:clear`
- Check Accept-Language header is being sent
- Verify translation keys exist in both language files

**Issue: RTL not working in dashboard**
- Check browser language settings
- Clear browser cache
- Verify Arabic is selected in dashboard language switcher

**Issue: Validation messages in wrong language**
- Ensure `Accept-Language` header is set correctly
- Check that validation.php exists for the requested language

## Summary

The application now fully supports Arabic and English with:
✅ API multilingual responses
✅ Dashboard multilingual interface
✅ RTL support for Arabic
✅ Automatic language detection
✅ Comprehensive translation files
✅ Validation messages in both languages
✅ Easy extensibility for more languages

All existing tests pass with the new multilingual implementation.
