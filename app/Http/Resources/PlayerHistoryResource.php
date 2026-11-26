<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $tournament = $this->tournament;
        $result = $this->result;

        return [
            'id' => $this->id,
            'joined_at' => $this->created_at?->toIso8601String(),
            'status' => $tournament?->status,
            'tournament_id' => $tournament?->id,
            'match_number' => $tournament?->match_number ?? ($tournament ? 'No.'.(206500 + $tournament->id) : null),
            'title' => $tournament?->title,
            'map' => $tournament?->map,
            'type' => $tournament?->type ?? 'Solo',
            'entry_fee' => $tournament?->entry_fee,
            'prize' => $tournament?->prize,
            'start_time' => $tournament?->start_time?->toIso8601String(),
            'position' => $result?->position,
            'kills' => $result?->kills,
            'won_prize' => $result?->prize,
        ];
    }
}
