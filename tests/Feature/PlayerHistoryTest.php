<?php

namespace Tests\Feature;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_history_returns_joined_matches_with_tournament_resource(): void
    {
        $player = User::create(['name' => 'History Player', 'phone' => '01700000001']);
        $tournament = Tournament::create([
            'title' => 'Completed Match',
            'map' => 'Bermuda',
            'start_time' => now()->subDay(),
            'status' => 'completed',
        ]);
        $tournament->registrations()->create(['user_id' => $player->id]);

        $response = $this->actingAs($player, 'sanctum')->getJson('/api/player-history');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $tournament->id)
            ->assertJsonPath('data.0.title', 'Completed Match')
            ->assertJsonPath('data.0.status', 'completed')
            ->assertJsonPath('data.0.is_joined', true)
            ->assertJsonPath('data.0.results', []);
    }

    public function test_player_history_requires_authentication(): void
    {
        $this->getJson('/api/player-history')->assertUnauthorized();
    }
}
