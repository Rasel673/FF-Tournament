<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'amount' => $this->signed_amount, // +100 / -50, ready for the green/red label
            'method' => $this->method,
            'status' => $this->status,        // pending | approved | rejected
            'created_at' => $this->created_at->toIso8601String(),
            'created_at_label' => $this->created_at->isToday()
                ? 'Today, '.$this->created_at->format('g:i A')
                : $this->created_at->format('d M, g:i A'),
        ];
    }
}
