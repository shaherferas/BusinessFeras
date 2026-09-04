<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Business extends Model
{
    use HasTranslations;

    protected $fillable = ['user_id', 'name', 'slug', 'description', 'name_translations', 'phone_number', 'whatsapp_number', 'category_id', 'subcategory_id', 'latitude', 'longitude', 'address_text', 'expires_at', 'status', 'approval_status', 'approved_at', 'rejection_reason', 'average_rating'];

    public $translatable = ['name_translations', 'description'];

    protected function casts(): array { return ['latitude' => 'decimal:8', 'longitude' => 'decimal:8', 'average_rating' => 'decimal:2', 'expires_at' => 'datetime', 'approved_at' => 'datetime']; }

    protected static function booted(): void
    {
        static::creating(function (Business $business): void {
            if (filled($business->slug)) {
                return;
            }

            $base = Str::slug($business->name) ?: 'business';
            $slug = $base;
            $suffix = 2;

            while (static::query()->where('slug', $slug)->exists()) {
                $slug = "{$base}-{$suffix}";
                $suffix++;
            }

            $business->slug = $slug;
        });
    }
    public function scopeActive(Builder $query): Builder { return $query->where('status', 'active')->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now())); }
    public function scopeWithinBounds(Builder $query, float $latitude, float $longitude, float $radiusKm): Builder
    {
        $latitudeDelta = $radiusKm / 111.045;
        $longitudeDelta = $radiusKm / max(111.045 * cos(deg2rad($latitude)), 0.0001);

        return $query->whereBetween('latitude', [$latitude - $latitudeDelta, $latitude + $latitudeDelta])
            ->whereBetween('longitude', [$longitude - $longitudeDelta, $longitude + $longitudeDelta]);
    }

    public function scopeWithinRadius(Builder $query, float $latitude, float $longitude, float $radiusKm): Builder
    {
        $query->withinBounds($latitude, $longitude, $radiusKm);
        $connection = $query->getModel()->getConnection()->getDriverName();

        return match ($connection) {
            'pgsql' => $query->whereRaw('ST_DWithin(ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?)', [$longitude, $latitude, $radiusKm * 1000]),
            'mysql', 'mariadb' => $query->whereRaw('ST_Distance_Sphere(POINT(longitude, latitude), POINT(?, ?)) <= ?', [$longitude, $latitude, $radiusKm * 1000]),
            default => $query,
        };
    }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function subcategory(): BelongsTo { return $this->belongsTo(Subcategory::class); }
    public function mediaPosts(): HasMany { return $this->hasMany(MediaPost::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }
    public function conversations(): HasMany { return $this->hasMany(Conversation::class); }
    public function interactions(): HasMany { return $this->hasMany(Interaction::class); }
    public function businessHours(): HasMany { return $this->hasMany(BusinessHour::class); }
    public function socialLinks(): HasMany { return $this->hasMany(BusinessSocialLink::class)->orderBy('sort_order'); }
    public function faqs(): HasMany { return $this->hasMany(BusinessFaq::class)->orderBy('sort_order'); }
    public function amenities(): BelongsToMany { return $this->belongsToMany(Amenity::class); }
}
