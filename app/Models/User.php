<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'uid', 'avatar', 'balance', 'password', 'is_admin', 'is_blocked',
        'is_moderator', 'moderator_balance', 'moderator_commission_rate',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_blocked' => 'boolean',
            'is_moderator' => 'boolean',
            'moderator_balance' => 'integer',
            'moderator_commission_rate' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Every player gets a unique 9-digit UID (shown in the app profile).
        static::creating(function (User $user) {
            if (! $user->uid) {
                do {
                    $uid = (string) random_int(100000000, 999999999);
                } while (static::where('uid', $uid)->exists());
                $user->uid = $uid;
            }
        });
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function moderatedTournaments(): HasMany
    {
        return $this->hasMany(Tournament::class, 'moderator_id')->latest();
    }

    public function moderatorRequests(): HasMany
    {
        return $this->hasMany(ModeratorRequest::class)->latest();
    }

    public function getTournamentsPlayedCountAttribute(): int
    {
        return $this->registrations()->count();
    }

    public function canApplyForModerator(): bool
    {
        $min = (int) Setting::get('min_tournaments_for_moderator', 5);
        return $this->tournaments_played_count >= $min;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
            return $this->avatar;
        }

        return asset('public/uploads/'.$this->avatar);
    }
}
