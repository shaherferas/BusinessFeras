<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Subcategory extends Model
{
    use HasTranslations;

    protected $fillable = ['category_id', 'name', 'slug', 'is_active', 'name_translations'];

    public $translatable = ['name_translations'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }
}
