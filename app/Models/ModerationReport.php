<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModerationReport extends Model
{
    protected $fillable = ['reported_by_user_id', 'reportable_type', 'reportable_id', 'reason', 'status'];

    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reported_by_user_id'); }
    public function reportable(): MorphTo { return $this->morphTo(); }
}
