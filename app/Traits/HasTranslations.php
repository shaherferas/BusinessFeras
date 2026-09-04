<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Trait HasTranslations
 *
 * Provides multilingual support for model attributes.
 * Stores translations as JSON in the database.
 *
 * Usage:
 * 1. Add JSON column to migration: $table->json('title')->nullable();
 * 2. Add to model: use HasTranslations;
 * 3. Define translatable attributes: protected $translatable = ['title', 'description'];
 *
 * Example:
 * $business->setTranslation('title', 'en', 'My Business');
 * $business->setTranslation('title', 'ar', 'عملي');
 * $business->getTranslation('title', 'en'); // "My Business"
 * $business->title; // Returns title in current locale
 */
trait HasTranslations
{
    /**
     * Get the translatable attributes for the model.
     *
     * @return array
     */
    public function getTranslatableAttributes(): array
    {
        return $this->translatable ?? [];
    }

    /**
     * Get a translation for a specific locale.
     *
     * @param string $attribute
     * @param string|null $locale
     * @param mixed $default
     * @return mixed
     */
    public function getTranslation(string $attribute, ?string $locale = null, $default = null): mixed
    {
        $locale = $locale ?? app()->getLocale();
        $translations = $this->getAttribute($attribute);

        if (is_string($translations)) {
            $translations = json_decode($translations, true);
        }

        if (!is_array($translations)) {
            return $default;
        }

        return $translations[$locale] ?? $translations[$this->getFallbackLocale()] ?? $default;
    }

    /**
     * Set a translation for a specific locale.
     *
     * @param string $attribute
     * @param string $locale
     * @param mixed $value
     * @return void
     */
    public function setTranslation(string $attribute, string $locale, mixed $value): void
    {
        $translations = $this->getAttribute($attribute);

        if (is_string($translations)) {
            $translations = json_decode($translations, true) ?? [];
        }

        if (!is_array($translations)) {
            $translations = [];
        }

        $translations[$locale] = $value;
        $this->attributes[$attribute] = json_encode($translations);
    }

    /**
     * Set multiple translations at once.
     *
     * @param string $attribute
     * @param array $translations
     * @return void
     */
    public function setTranslations(string $attribute, array $translations): void
    {
        $this->attributes[$attribute] = json_encode($translations);
    }

    /**
     * Get all translations for an attribute.
     *
     * @param string $attribute
     * @return array
     */
    public function getTranslations(string $attribute): array
    {
        $translations = $this->getAttribute($attribute);

        if (is_string($translations)) {
            return json_decode($translations, true) ?? [];
        }

        return is_array($translations) ? $translations : [];
    }

    /**
     * Get the fallback locale.
     *
     * @return string
     */
    protected function getFallbackLocale(): string
    {
        return config('app.fallback_locale', 'en');
    }

    /**
     * Create a dynamic accessor for translatable attributes.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (in_array($key, $this->getTranslatableAttributes())) {
            return $this->getTranslation($key);
        }

        return parent::__get($key);
    }

    /**
     * Create a dynamic setter for translatable attributes.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        if (in_array($key, $this->getTranslatableAttributes())) {
            if (is_array($value)) {
                $this->setTranslations($key, $value);
            } else {
                $this->setTranslation($key, app()->getLocale(), $value);
            }
            return;
        }

        parent::__set($key, $value);
    }
}