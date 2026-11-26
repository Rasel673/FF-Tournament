<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $count = $this->tournaments_count ?? ($this->relationLoaded('tournaments') ? $this->tournaments->count() : 0);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'image_url' => $this->image_url,
            'short_description' => $this->short_description,
            'order' => $this->order,
            'tournaments_count' => $count,
            'matches_count' => $count,
            'matches_label' => "{$count} matches available",
            'subtitle' => "{$count} matches available",
            'tournaments' => TournamentResource::collection($this->whenLoaded('tournaments')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

