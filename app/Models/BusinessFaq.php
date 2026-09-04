<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class BusinessFaq extends Model
{
    use HasTranslations;

    protected $fillable = ['business_id', 'question', 'answer', 'answer_translations', 'locale', 'sort_order'];

    public $translatable = ['question', 'answer_translations'];

    protected function casts(): array { return []; }

    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
}
