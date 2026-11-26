<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModeratorRequest extends Model
{
    protected $fillable = [
        'user_id',
        'tournaments_played',
        'phone',
        'note',
        'admin_note',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'tournaments_played' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
