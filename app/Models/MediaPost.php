<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaPost extends Model
{
    use SoftDeletes;

    protected $fillable = ['business_id', 'type', 'file_path', 'thumbnail_path', 'caption', 'moderation_status', 'expires_at', 'likes_count', 'comments_count', 'views_count'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(MediaComment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(MediaLike::class);
    }
}
