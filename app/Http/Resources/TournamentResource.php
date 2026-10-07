<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // registrations_count / is_joined come from Tournament::withUserState()
        $filled = $this->registrations_count ?? $this->registrations()->count();
        $joined = (bool) ($this->is_joined ?? false);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'map' => $this->map,
            'entry_fee' => $this->entry_fee,
            'prize' => $this->prize,
            'per_kill' => $this->per_kill,
            'status' => $this->status,
            'start_time' => $this->start_time?->toIso8601String(),
            'start_time_label' => $this->start_time?->format('g:i A'),
            'opens_at_label' => $this->open_time?->format('g:i A'),
            'slots' => $this->slots,
            'slots_filled' => $filled,
            'is_joined' => $joined,
            'can_join' => $this->status === 'active' && ! $joined && $filled < $this->slots,

            // Room info is only revealed to players who joined the tournament.
            'room_id' => $joined ? $this->room_id : null,
            'room_password' => $joined ? $this->room_password : null,

            // Result & proof (only filled once the match is completed).
            'result_note' => $this->result_note,
            'proof_image' => $this->proof_image ? asset('storage/'.$this->proof_image) : null,
            'results' => ResultResource::collection($this->whenLoaded('results')),
        ];
    }
}
