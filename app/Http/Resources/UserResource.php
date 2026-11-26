<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'uid' => $this->uid,
            'phone' => $this->phone,
            'avatar' => $this->avatar_url,
            'balance' => $this->balance,
            'balance_formatted' => $this->balance . ' BDT',
            'is_moderator' => (bool) $this->is_moderator,
            'moderator_balance' => (int) ($this->moderator_balance ?? 0),
            'tournaments_played' => $this->tournaments_played_count,
            'can_apply_moderator' => $this->canApplyForModerator(),
            'has_unread_notifications' => false,
            'notifications_count' => 0,
        ];
    }
}
