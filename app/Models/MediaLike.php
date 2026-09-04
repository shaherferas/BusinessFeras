<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaLike extends Model
{
    protected $fillable = ['media_post_id', 'user_id'];

    public function mediaPost(): BelongsTo
    {
        return $this->belongsTo(MediaPost::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
