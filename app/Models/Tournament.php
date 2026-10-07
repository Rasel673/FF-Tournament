<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    protected $fillable = [
        'title', 'map', 'entry_fee', 'prize', 'per_kill', 'slots',
        'start_time', 'open_time', 'status', 'room_id', 'room_password',
        'proof_image', 'result_note',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'open_time' => 'datetime',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class)->orderByRaw('position is null, position asc');
    }

    /** Adds registrations_count and is_joined (for the given user) to the query. */
    public function scopeWithUserState(Builder $query, int $userId): Builder
    {
        return $query->withCount('registrations')
            ->withExists(['registrations as is_joined' => fn ($q) => $q->where('user_id', $userId)]);
    }
}
