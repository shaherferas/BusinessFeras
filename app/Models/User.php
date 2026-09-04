<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'email_verified_at',
        'password',
        'avatar_url',
        'is_business_owner',
        'active_mode',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_business_owner' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function businesses(): HasMany { return $this->hasMany(Business::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }
    public function interactions(): HasMany { return $this->hasMany(Interaction::class); }
    public function conversations(): \Illuminate\Database\Eloquent\Relations\BelongsToMany { return $this->belongsToMany(Conversation::class, 'conversation_participants')->withTimestamps(); }
    public function sentMessages(): HasMany { return $this->hasMany(Message::class, 'sender_id'); }
    public function canAccessPanel(Panel $panel): bool { return $panel->getId() === 'admin' && $this->hasRole('Super Admin'); }
}
