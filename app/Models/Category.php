<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['name', 'slug', 'icon', 'parent_id', 'is_active', 'name_translations'];

    public $translatable = ['name_translations'];

    protected function casts(): array { return ['is_active' => 'boolean']; }

    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id'); }
    public function subcategories(): HasMany { return $this->hasMany(Subcategory::class); }
    public function businesses(): HasMany { return $this->hasMany(Business::class); }
}
