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
        $needed = max(0, $this->slots - $filled);
        $startsIn = $this->formatted_starts_in;

        return [
            'id' => $this->id,
            'match_number' => $this->match_number ?? ('No.' . (206500 + $this->id)),
            'title' => $this->title,
            'category_id' => $this->category_id,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'title' => $this->category->title,
                'image_url' => $this->category->image_url,
            ] : null,

            // Badges & Game details (as seen on Match Card)
            'map' => $this->map,
            'map_label' => 'Map: ' . $this->map,
            'type' => $this->type ?? 'Solo',
            'type_label' => 'Type: ' . ($this->type ?? 'Solo'),

            // Financial & Prize Metrics
            'entry_fee' => $this->entry_fee,
            'entry_fee_label' => 'Entry: ' . $this->entry_fee . ' BDT',
            'prize' => $this->prize,
            'winning_prize' => $this->prize,
            'prize_label' => 'Prize: ' . $this->prize . ' BDT',
            'per_kill' => $this->per_kill,
            'per_kill_label' => 'Per Kill: ' . $this->per_kill . ' BDT',

            // Slots & Progress Bar (e.g. Slots: 24/48, 24 Players Needed)
            'slots' => $this->slots,
            'slots_filled' => $filled,
            'slots_label' => "Slots: {$filled}/{$this->slots}",
            'players_needed' => $needed,
            'players_needed_label' => "{$needed} Players Needed",
            'spots_available' => $needed,
            'spots_available_label' => "{$needed} Spots available",
            'progress_percentage' => $this->slots > 0 ? (int) round(($filled / $this->slots) * 100) : 0,

            // Timings & Live Countdown (STARTS IN: 6h : 59m : 2s)
            'status' => $this->status,
            'start_time' => $this->start_time?->toIso8601String(),
            'start_time_formatted' => $this->start_time?->format('d M Y, h:i A'),
            'start_time_label' => $this->start_time?->format('g:i A'),
            'opens_at_label' => $this->open_time?->format('g:i A'),
            'starts_in' => $startsIn,

            // Prize Pool Bottom Sheet Breakdown (Image 4)
            'prize_pool_title' => 'Tournament Prize Pool',
            'prize_pool_subtitle' => $this->title . ' || ' . ($this->match_number ?? ('Match No.' . (206500 + $this->id))),
            'total_prize_pool' => $this->prize,
            'total_prize_pool_label' => $this->prize . ' Taka',
            'prize_pool_distribution' => $this->prize_pool_list,

            // Rules & Regulations & Tutorial Video (Image 5)
            'rules_title' => $this->rules_title ?: 'SQUAD SCRIM MATCH RULES',
            'rules' => $this->rules_list,
            'tutorial_video_url' => $this->tutorial_video_url,

            // Join State
            'is_joined' => $joined,
            'can_join' => $this->status === 'active' && ! $joined && $filled < $this->slots,

            // Room info is only revealed to players who joined the tournament.
            'room_id' => $joined ? $this->room_id : null,
            'room_password' => $joined ? $this->room_password : null,

            // Result & proof (only filled once the match is completed or under review).
            'result_note' => $this->result_note,
            'admin_review_note' => $this->admin_review_note,
            'proof_image' => $this->proof_image_url,
            'results' => ResultResource::collection($this->whenLoaded('results')),
        ];
    }
}
