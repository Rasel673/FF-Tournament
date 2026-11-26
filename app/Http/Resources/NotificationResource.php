<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'large_image' => $this->large_image_url ?? $this->large_image,
            'small_icon' => $this->small_icon_url ?? $this->small_icon,
            'small_image' => $this->small_icon_url ?? $this->small_icon,
            'target' => $this->target,
            'data' => $this->data,
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_label' => $this->created_at?->diffForHumans(),
        ];
    }
}
