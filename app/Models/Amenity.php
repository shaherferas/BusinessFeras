<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Amenity extends Model
{
    use HasTranslations;

    protected $fillable = ['name', 'slug', 'icon', 'is_active', 'name_translations'];

    public $translatable = ['name_translations'];

    protected function casts(): array { return ['is_active' => 'boolean']; }

    public function businesses(): BelongsToMany { return $this->belongsToMany(Business::class); }
}
