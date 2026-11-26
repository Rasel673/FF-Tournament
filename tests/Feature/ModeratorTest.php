<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ModeratorRequest;
use App\Models\PaymentMethod;
use App\Models\Registration;
use App\Models\Setting;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ModeratorTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin User',
            'phone' => '01700000000',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);
    }

    private function createPlayer(string $phone = '01811111111', string $name = 'Player One'): User
    {
        return User::create([
            'name' => $name,
            'phone' => $phone,
            'password' => bcrypt('password'),
            'is_admin' => false,
            'is_moderator' => false,
            'uid' => (string) random_int(100000000, 999999999),
            'balance' => 500,
        ]);
    }

    private function createModerator(string $phone = '01899999999', string $name = 'Mod User'): User
    {
        return User::create([
            'name' => $name,
            'phone' => $phone,
            'password' => bcrypt('password'),
            'is_admin' => false,
            'is_moderator' => true,
            'uid' => (string) random_int(100000000, 999999999),
            'balance' => 200,
            'moderator_balance' => 200,
        ]);
    }

    public function test_player_cannot_apply_if_less_than_5_tournaments_played(): void
    {
        $player = $this->createPlayer();

        // Player played only 2 tournaments
        for ($i = 0; $i < 2; $i++) {
            $t = Tournament::create([
                'title' => "Match {$i}",
                'map' => 'Bermuda',
                'entry_fee' => 10,
                'prize' => 100,
                'slots' => 48,
                'start_time' => now()->addHours(2),
            ]);
            Registration::create([
                'tournament_id' => $t->id,
                'user_id' => $player->id,
                'in_game_name' => 'Gamer',
                'in_game_uid' => '12345',
            ]);
        }

        $response = $this->actingAs($player, 'sanctum')->postJson('/api/moderator/apply', [
            'note' => 'I want to be moderator',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'You must play at least 5 tournaments to apply for moderator. You have played 2 tournament(s).']);
    }

    public function test_player_can_apply_after_5_tournaments(): void
    {
        $player = $this->createPlayer('01822222222', 'Active Player');

        // Play 5 tournaments
        for ($i = 0; $i < 5; $i++) {
            $t = Tournament::create([
                'title' => "Match {$i}",
                'map' => 'Bermuda',
                'entry_fee' => 10,
                'prize' => 100,
                'slots' => 48,
                'start_time' => now()->addHours(2),
            ]);
            Registration::create([
                'tournament_id' => $t->id,
                'user_id' => $player->id,
                'in_game_name' => 'ProGamer',
                'in_game_uid' => '123456',
            ]);
        }

        $response = $this->actingAs($player, 'sanctum')->postJson('/api/moderator/apply', [
            'note' => 'Active tournament player ready to arrange matches.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('moderator_requests', [
            'user_id' => $player->id,
            'tournaments_played' => 5,
            'status' => 'pending',
        ]);

        // Duplicate application should be blocked
        $dupResponse = $this->actingAs($player, 'sanctum')->postJson('/api/moderator/apply', [
            'note' => 'Another request',
        ]);
        $dupResponse->assertStatus(422);
    }

    public function test_admin_can_approve_and_reject_moderator_requests(): void
    {
        $admin = $this->createAdmin();
        $player = $this->createPlayer('01833333333', 'Applicant');

        $req = ModeratorRequest::create([
            'user_id' => $player->id,
            'tournaments_played' => 6,
            'phone' => $player->phone,
            'status' => 'pending',
        ]);

        Http::fake();

        // Approve
        $response = $this->actingAs($admin)->post("/admin/moderators/requests/{$req->id}/approve");
        $response->assertRedirect();

        $this->assertTrue($player->fresh()->is_moderator);
        $this->assertEquals('approved', $req->fresh()->status);

        // Revoke
        $this->actingAs($admin)->post("/admin/moderators/{$player->id}/revoke");
        $this->assertFalse($player->fresh()->is_moderator);
    }

    public function test_admin_can_directly_promote_player_to_moderator(): void
    {
        $admin = $this->createAdmin();
        $player = $this->createPlayer('01844444444', 'Direct Promote');

        Http::fake();

        $response = $this->actingAs($admin)->post('/admin/moderators', [
            'user_id' => $player->id,
            'moderator_commission_rate' => 75,
        ]);

        $response->assertRedirect();
        $this->assertTrue($player->fresh()->is_moderator);
        $this->assertEquals(75, $player->fresh()->moderator_commission_rate);
    }

    public function test_moderator_can_arrange_tournament_and_set_room_details(): void
    {
        $mod = $this->createModerator();
        $player = $this->createPlayer('01855555555', 'Joined Player');

        Http::fake();

        // 1. Moderator creates tournament
        $createResponse = $this->actingAs($mod, 'sanctum')->postJson('/api/moderator/tournaments', [
            'title' => 'MODERATOR SCRIM MATCH',
            'map' => 'Purgatory',
            'type' => 'Squad',
            'entry_fee' => 20,
            'prize' => 500,
            'slots' => 48,
            'start_time' => now()->addHours(3)->toDateTimeString(),
        ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('data.title', 'MODERATOR SCRIM MATCH');

        $tournamentId = $createResponse->json('data.id');
        $tournament = Tournament::findOrFail($tournamentId);
        $this->assertEquals($mod->id, $tournament->moderator_id);

        // Player joins tournament
        Registration::create([
            'tournament_id' => $tournament->id,
            'user_id' => $player->id,
            'in_game_name' => 'Player1',
            'in_game_uid' => '99999',
        ]);

        // 2. Moderator enters room ID and password
        $roomResponse = $this->actingAs($mod, 'sanctum')->putJson("/api/moderator/tournaments/{$tournament->id}/room", [
            'room_id' => '12345678',
            'room_password' => 'freefire99',
        ]);

        $roomResponse->assertStatus(200)
            ->assertJsonPath('data.room_id', '12345678')
            ->assertJsonPath('data.players_notified', 1);

        $this->assertEquals('12345678', $tournament->fresh()->room_id);
    }

    public function test_moderator_stores_results_and_receives_commission(): void
    {
        $mod = $this->createModerator('01866666666', 'Mod In Charge');
        $player = $this->createPlayer('01877777777', 'Winner Player');
        $initialModBalance = $mod->moderator_balance;
        $initialPlayerBalance = $player->balance;

        $tournament = Tournament::create([
            'title' => 'CHAMPIONSHIP MATCH',
            'map' => 'Bermuda',
            'entry_fee' => 25,
            'prize' => 400,
            'per_kill' => 10,
            'slots' => 48,
            'start_time' => now()->addHours(1),
            'moderator_id' => $mod->id,
            'moderator_commission' => 60,
            'status' => 'active',
        ]);

        Registration::create([
            'tournament_id' => $tournament->id,
            'user_id' => $player->id,
            'in_game_name' => 'Champ',
            'in_game_uid' => '88888',
        ]);

        Http::fake();

        // Moderator submits results for admin approval
        $response = $this->actingAs($mod, 'sanctum')->postJson("/api/moderator/tournaments/{$tournament->id}/results", [
            'results' => [
                [
                    'user_id' => $player->id,
                    'position' => 1,
                    'kills' => 5,
                    'prize' => 350,
                ],
            ],
            'result_note' => 'Great match, fair play.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'pending_approval');

        $this->assertEquals('pending_approval', $tournament->fresh()->status);

        // Balances NOT credited yet
        $this->assertEquals($initialPlayerBalance, $player->fresh()->balance);
        $this->assertEquals($initialModBalance, $mod->fresh()->moderator_balance);

        // Admin approves results and sends reward money
        $admin = $this->createAdmin();
        $approveResponse = $this->actingAs($admin, 'sanctum')->postJson("/api/tournaments/{$tournament->id}/approve-results");
        $approveResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'completed');

        // Player received prize: 350 + (5 * 10) = 400 BDT
        $this->assertEquals($initialPlayerBalance + 400, $player->fresh()->balance);

        // Moderator received 60 BDT commission
        $this->assertEquals($initialModBalance + 60, $mod->fresh()->moderator_balance);
        $this->assertTrue($tournament->fresh()->is_commission_paid);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $mod->id,
            'type' => 'commission',
            'amount' => 60,
            'tournament_id' => $tournament->id,
        ]);
    }

    public function test_moderator_can_check_earnings_and_withdraw(): void
    {
        $mod = $this->createModerator('01888888888', 'Mod Earner');
        $mod->update(['balance' => 500, 'moderator_balance' => 500]);

        $pm = PaymentMethod::create([
            'name' => 'bKash',
            'account_number' => '01888888888',
            'is_active' => true,
        ]);

        // Earnings
        $earnResponse = $this->actingAs($mod, 'sanctum')->getJson('/api/moderator/earnings');
        $earnResponse->assertStatus(200)
            ->assertJsonPath('data.moderator_balance', 500);

        // Withdraw
        $withdrawResponse = $this->actingAs($mod, 'sanctum')->postJson('/api/moderator/withdraw', [
            'amount' => 200,
            'method' => 'bKash',
            'account_number' => '01888888888',
        ]);

        $withdrawResponse->assertStatus(201);
        $this->assertEquals(300, $mod->fresh()->balance);
        $this->assertEquals(300, $mod->fresh()->moderator_balance);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $mod->id,
            'type' => 'withdraw',
            'amount' => 200,
            'method' => 'bKash',
            'status' => 'pending',
        ]);
    }
}
