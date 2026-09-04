<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BusinessSocialLink extends Model { protected $fillable=['business_id','platform','url','sort_order']; public function business(): BelongsTo{return $this->belongsTo(Business::class);} }
