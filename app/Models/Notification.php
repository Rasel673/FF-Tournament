<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'target',
        'external_id',
        'title',
        'description',
        'large_image',
        'small_icon',
        'small_image',
        'data',
        'onesignal_id',
        'recipients_count',
        'status',
        'error_message',
    ];

    protected $casts = [
        'data' => 'array',
        'recipients_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getLargeImageUrlAttribute(): ?string
    {
        if (!$this->large_image) {
            return null;
        }

        if (str_starts_with($this->large_image, 'http://') || str_starts_with($this->large_image, 'https://')) {
            return $this->large_image;
        }

        return asset('storage/' . $this->large_image);
    }

    public function setSmallImageAttribute($value): void
    {
        $this->attributes['small_icon'] = $value;
    }

    public function getSmallIconUrlAttribute(): ?string
    {
        $icon = $this->small_icon ?? $this->attributes['small_icon'] ?? null;
        if (!$icon) {
            return null;
        }

        if (str_starts_with($icon, 'http://') || str_starts_with($icon, 'https://')) {
            return $icon;
        }

        return asset('storage/' . $icon);
    }

    public function getSmallImageUrlAttribute(): ?string
    {
        return $this->getSmallIconUrlAttribute();
    }
}
