<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /** Types that add money to the wallet. Everything else subtracts. */
    public const CREDIT_TYPES = ['deposit', 'prize', 'bonus'];

    protected $fillable = [
        'user_id', 'type', 'amount', 'method', 'trx_id', 'account_number',
        'screenshot', 'status', 'tournament_id', 'note',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function getSignedAmountAttribute(): int
    {
        return in_array($this->type, self::CREDIT_TYPES, true) ? $this->amount : -$this->amount;
    }

    public function getTitleAttribute(): string
    {
        return match ($this->type) {
            'deposit' => 'Deposit via '.$this->method,
            'withdraw' => 'Withdraw to '.$this->method,
            'tournament_entry' => $this->tournament?->title ?? 'Tournament entry',
            'prize' => 'Prize: '.($this->tournament?->title ?? 'Tournament'),
            'bonus' => 'Admin bonus',
            'penalty' => 'Admin deduction',
            default => ucfirst($this->type),
        };
    }

    public function getScreenshotUrlAttribute(): ?string
    {
        return $this->screenshot ? asset('storage/'.$this->screenshot) : null;
    }
}
