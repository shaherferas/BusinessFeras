<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaComment extends Model
{
    use SoftDeletes;

    protected $fillable = ['media_post_id', 'user_id', 'content'];

    public function mediaPost(): BelongsTo
    {
        return $this->belongsTo(MediaPost::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
