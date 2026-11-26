<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    protected $fillable = [
        'match_number',
        'category_id',
        'moderator_id',
        'title',
        'map',
        'type',
        'entry_fee',
        'prize',
        'moderator_commission',
        'is_commission_paid',
        'prize_pool_distribution',
        'per_kill',
        'slots',
        'start_time',
        'open_time',
        'status',
        'room_id',
        'room_password',
        'proof_image',
        'result_note',
        'admin_review_note',
        'tutorial_video_url',
        'rules_title',
        'rules',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'open_time' => 'datetime',
            'prize_pool_distribution' => 'array',
            'moderator_commission' => 'integer',
            'is_commission_paid' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Tournament $tournament) {
            if (empty($tournament->match_number)) {
                $nextId = (static::max('id') ?? 0) + 1;
                $tournament->match_number = 'No.' . (206500 + $nextId);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
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

    public function getProofImageUrlAttribute(): ?string
    {
        if (! $this->proof_image) {
            return null;
        }

        if (str_starts_with($this->proof_image, 'http://') || str_starts_with($this->proof_image, 'https://')) {
            return $this->proof_image;
        }

        return asset('public/uploads/'.$this->proof_image);
    }

    public function getFormattedStartsInAttribute(): array
    {
        $diff = 0;
        if ($this->start_time) {
            $diff = max(0, now()->diffInSeconds($this->start_time, false));
        }

        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);
        $seconds = $diff % 60;

        return [
            'total_seconds' => (int) $diff,
            'hours' => (int) $hours,
            'minutes' => (int) $minutes,
            'seconds' => (int) $seconds,
            'formatted' => sprintf('%dh : %02dm : %02ds', $hours, $minutes, $seconds),
        ];
    }

    public function getRulesListAttribute(): array
    {
        if (! empty($this->rules)) {
            if (is_array($this->rules)) {
                return $this->rules;
            }
            $lines = array_values(array_filter(array_map('trim', explode("\n", $this->rules))));
            if (! empty($lines)) {
                return $lines;
            }
        }

        return [
            'ম্যাচে জয়েন করার সময় আপনার গেম আইডি ভালোভাবে মিলিয়ে নিন। গেম আইডির পরিবর্তে অন্য কিছু লিখলে ম্যাচ খেলতে দেওয়া হবে না।',
            'স্কোয়াডের সব সদস্যের গেম আইডির লেভেল কমপক্ষে ৪০ হতে হবে। লেভেল ৪০-এর নিচে হলে ম্যাচ খেলতে দেওয়া হবে না।',
            'Custom Room-এ জয়েন করার সময় স্কোয়াডের সবাইকে একসঙ্গে গ্রুপে নিয়ে জয়েন করতে হবে। আলাদা করে জয়েন করার কারণে অন্য দলের প্লেয়ার আপনার দলে ঢুকে গেলে কর্তৃপক্ষ দায়ী থাকবে না।',
        ];
    }

    public function getPrizePoolListAttribute(): array
    {
        if (! empty($this->prize_pool_distribution) && is_array($this->prize_pool_distribution)) {
            return $this->prize_pool_distribution;
        }

        if ($this->prize > 0) {
            $first = (int) round($this->prize * 0.41);
            $second = (int) round($this->prize * 0.28);
            $third = (int) round($this->prize * 0.20);
            $fourth = max(0, $this->prize - $first - $second - $third);

            return [
                ['position' => 'Winner', 'prize' => $first, 'prize_formatted' => $first . ' Taka'],
                ['position' => '2nd Position', 'prize' => $second, 'prize_formatted' => $second . ' Taka'],
                ['position' => '3rd Position', 'prize' => $third, 'prize_formatted' => $third . ' Taka'],
                ['position' => '4th Position', 'prize' => $fourth, 'prize_formatted' => $fourth . ' Taka'],
            ];
        }

        return [];
    }

    public function getTotalEntryPoolAttribute(): int
    {
        return (int) ($this->slots * $this->entry_fee);
    }

    public function getActualEntryPoolAttribute(): int
    {
        $count = $this->registrations_count ?? $this->registrations()->count();
        return (int) ($count * $this->entry_fee);
    }

    public function getRewardPool70Attribute(): int
    {
        return (int) round($this->total_entry_pool * 0.70);
    }

    public function getModeratorShare10Attribute(): int
    {
        return (int) round($this->total_entry_pool * 0.10);
    }

    public function getAdminShare20Attribute(): int
    {
        return (int) round($this->total_entry_pool * 0.20);
    }
}
