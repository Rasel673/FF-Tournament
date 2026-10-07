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
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_blocked' => 'boolean',
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

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('storage/'.$this->avatar) : null;
    }
}
