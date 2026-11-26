<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Tournament;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModeratorPanelTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '01700000000',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);
    }

    private function createModerator(string $phone = '01833025822', string $name = 'Mod Shakib'): User
    {
        return User::create([
            'name' => $name,
            'phone' => $phone,
            'password' => bcrypt('modpass123'),
            'is_admin' => false,
            'is_moderator' => true,
            'moderator_balance' => 200,
            'moderator_commission_rate' => 60,
        ]);
    }

    private function createPlayer(string $phone = '01900000000'): User
    {
        return User::create([
            'name' => 'Normal Player',
            'phone' => $phone,
            'password' => bcrypt('playerpass'),
            'is_admin' => false,
            'is_moderator' => false,
        ]);
    }

    public function test_admin_can_create_new_moderator_account(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.moderators.create-account'), [
            'name' => 'New Moderator Rahim',
            'phone' => '01855555555',
            'password' => 'secret123',
            'email' => 'rahim@mod.com',
            'moderator_commission_rate' => 75,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'New Moderator Rahim',
            'phone' => '01855555555',
            'email' => 'rahim@mod.com',
            'is_moderator' => true,
            'is_admin' => false,
            'moderator_commission_rate' => 75,
        ]);
    }

    public function test_moderator_can_login_with_phone_and_password(): void
    {
        $moderator = $this->createModerator('01833025822', 'Mod Shakib');

        $response = $this->post(route('admin.login.submit'), [
            'login' => '01833025822',
            'password' => 'modpass123',
        ]);

        $response->assertRedirect(route('admin.tournaments.index'));
        $this->assertAuthenticatedAs($moderator);
    }

    public function test_admin_can_login_with_email_and_password(): void
    {
        $admin = $this->createAdmin();

        $response = $this->post(route('admin.login.submit'), [
            'login' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_regular_player_cannot_login_to_admin_panel(): void
    {
        $player = $this->createPlayer('01900000000');

        $response = $this->post(route('admin.login.submit'), [
            'login' => '01900000000',
            'password' => 'playerpass',
        ]);

        $response->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_moderator_only_sees_their_assigned_tournaments(): void
    {
        $mod1 = $this->createModerator('01811111111', 'Mod 1');
        $mod2 = $this->createModerator('01822222222', 'Mod 2');

        $t1 = Tournament::create([
            'title' => 'Mod 1 Match',
            'map' => 'Bermuda',
            'start_time' => now()->addDay(),
            'entry_fee' => 50,
            'prize' => 500,
            'slots' => 50,
            'moderator_id' => $mod1->id,
        ]);

        $t2 = Tournament::create([
            'title' => 'Mod 2 Match',
            'map' => 'Purgatory',
            'start_time' => now()->addDay(),
            'entry_fee' => 50,
            'prize' => 500,
            'slots' => 50,
            'moderator_id' => $mod2->id,
        ]);

        $response = $this->actingAs($mod1)->get(route('admin.tournaments.index'));
        $response->assertOk();
        $response->assertSee('Mod 1 Match');
        $response->assertDontSee('Mod 2 Match');
    }

    public function test_moderator_is_blocked_from_admin_only_routes(): void
    {
        $moderator = $this->createModerator();

        // Trying to access dashboard
        $response = $this->actingAs($moderator)->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.tournaments.index'));
        $response->assertSessionHas('error');

        // Trying to access categories
        $response = $this->actingAs($moderator)->get(route('admin.categories.index'));
        $response->assertRedirect(route('admin.tournaments.index'));

        // Trying to access user management
        $response = $this->actingAs($moderator)->get(route('admin.users.index'));
        $response->assertRedirect(route('admin.tournaments.index'));

        // Trying to access moderators management
        $response = $this->actingAs($moderator)->get(route('admin.moderators.index'));
        $response->assertRedirect(route('admin.tournaments.index'));

        // Trying to access settings
        $response = $this->actingAs($moderator)->get(route('admin.settings.edit'));
        $response->assertRedirect(route('admin.tournaments.index'));
    }

    public function test_moderator_cannot_edit_another_moderators_tournament(): void
    {
        $mod1 = $this->createModerator('01811111111', 'Mod 1');
        $mod2 = $this->createModerator('01822222222', 'Mod 2');

        $t2 = Tournament::create([
            'title' => 'Mod 2 Match',
            'map' => 'Purgatory',
            'start_time' => now()->addDay(),
            'entry_fee' => 50,
            'prize' => 500,
            'slots' => 50,
            'moderator_id' => $mod2->id,
        ]);

        $response = $this->actingAs($mod1)->get(route('admin.tournaments.edit', $t2));
        $response->assertStatus(403);
    }

    public function test_moderator_can_view_earnings_and_request_withdrawal(): void
    {
        $mod = $this->createModerator('01833025822', 'Mod Shakib');
        $mod->update(['moderator_balance' => 150]);

        $response = $this->actingAs($mod)->get(route('admin.moderator.earnings'));
        $response->assertOk();
        $response->assertSee('৳150');

        $postWithdraw = $this->actingAs($mod)->post(route('admin.moderator.withdraw'), [
            'method' => 'bKash',
            'account_number' => '01833025822',
            'amount' => 100,
        ]);

        $postWithdraw->assertRedirect();
        $postWithdraw->assertSessionHas('success');

        $this->assertEquals(50, $mod->fresh()->moderator_balance);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $mod->id,
            'type' => 'moderator_withdraw',
            'amount' => 100,
            'method' => 'bKash',
            'account_number' => '01833025822',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_reject_moderator_withdraw_and_refunds_balance(): void
    {
        $admin = $this->createAdmin();
        $mod = $this->createModerator('01833025822', 'Mod Shakib');
        $mod->update(['moderator_balance' => 50]);

        $tx = Transaction::create([
            'user_id' => $mod->id,
            'type' => 'moderator_withdraw',
            'amount' => 100,
            'method' => 'bKash',
            'account_number' => '01833025822',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.transactions.reject', $tx), [
            'note' => 'Invalid number',
        ]);

        $response->assertRedirect();
        $this->assertEquals('rejected', $tx->fresh()->status);
        // 50 + 100 = 150 refunded
        $this->assertEquals(150, $mod->fresh()->moderator_balance);
    }

    public function test_tournament_revenue_split_70_percent_reward_10_percent_moderator_20_percent_admin(): void
    {
        $admin = $this->createAdmin();
        $admin->update(['balance' => 0]);

        $mod = $this->createModerator('01833025822', 'Mod Shakib');
        $mod->update(['balance' => 0, 'moderator_balance' => 0]);

        // Create tournament with entry_fee = 100
        $tournament = Tournament::create([
            'title' => 'Revenue Split Test Match',
            'map' => 'Bermuda',
            'start_time' => now()->addHours(2),
            'entry_fee' => 100,
            'prize' => 700, // 70% of 1,000
            'slots' => 10,
            'moderator_id' => $mod->id,
            'status' => 'active',
        ]);

        // Register 10 players -> total entry pool = 10 * 100 = 1,000 BDT
        $players = [];
        for ($i = 1; $i <= 10; $i++) {
            $p = User::create([
                'name' => "Player $i",
                'phone' => "0178888880$i",
                'password' => bcrypt('pass'),
                'balance' => 0,
            ]);
            $tournament->registrations()->create(['user_id' => $p->id]);
            $players[] = $p;
        }

        // 1. Moderator submits match results
        $results = [];
        $results[$players[0]->id] = ['position' => 1, 'kills' => 5, 'prize' => 400];
        $results[$players[1]->id] = ['position' => 2, 'kills' => 3, 'prize' => 200];
        $results[$players[2]->id] = ['position' => 3, 'kills' => 2, 'prize' => 100];

        $response = $this->actingAs($mod)->post(route('admin.tournaments.results.store', $tournament), [
            'results' => $results,
            'result_note' => 'Tournament concluded successfully',
        ]);

        $response->assertRedirect();
        $tournament->refresh();

        // Status is now pending_approval, NOT completed yet!
        $this->assertEquals('pending_approval', $tournament->status);

        // Funds must NOT be disbursed yet
        $this->assertEquals(0, $mod->fresh()->moderator_balance);
        $this->assertEquals(0, $mod->fresh()->balance);
        $this->assertEquals(0, $admin->fresh()->balance);
        $this->assertEquals(0, $players[0]->fresh()->balance);

        // Moderator cannot approve results (AdminOnly middleware blocks)
        $modApprove = $this->actingAs($mod)->post(route('admin.tournaments.approve-results', $tournament));
        $modApprove->assertRedirect(route('admin.tournaments.index'));
        $modApprove->assertSessionHas('error');
        $this->assertEquals('pending_approval', $tournament->fresh()->status);

        // 2. Admin approves results and disburses reward money
        $adminApprove = $this->actingAs($admin)->post(route('admin.tournaments.approve-results', $tournament));
        $adminApprove->assertRedirect(route('admin.tournaments.index'));
        $adminApprove->assertSessionHas('success');

        $tournament->refresh();
        $this->assertEquals('completed', $tournament->status);

        // 1. Moderator gets 10% of 1,000 = 100 BDT
        $this->assertEquals(100, $mod->fresh()->moderator_balance);
        $this->assertEquals(100, $mod->fresh()->balance);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $mod->id,
            'type' => 'commission',
            'amount' => 100,
            'tournament_id' => $tournament->id,
            'status' => 'approved',
        ]);

        // 2. Admin gets 20% of 1,000 = 200 BDT
        $this->assertEquals(200, $admin->fresh()->balance);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $admin->id,
            'type' => 'admin_income',
            'amount' => 200,
            'tournament_id' => $tournament->id,
            'status' => 'approved',
        ]);

        // 3. Player 1 gets prize + kills (400)
        $this->assertEquals(400, $players[0]->fresh()->balance);
    }

    public function test_admin_can_reject_tournament_results_with_revision_note(): void
    {
        $admin = $this->createAdmin();
        $mod = $this->createModerator('01855555555', 'Mod Tester');

        $tournament = Tournament::create([
            'title' => 'Rejection Test Match',
            'map' => 'Bermuda',
            'start_time' => now()->addHours(2),
            'entry_fee' => 50,
            'prize' => 350,
            'slots' => 10,
            'moderator_id' => $mod->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.tournaments.reject-results', $tournament), [
            'admin_review_note' => 'UID on proof screenshot does not match the winner.',
        ]);

        $response->assertRedirect(route('admin.tournaments.index'));
        $tournament->refresh();

        $this->assertEquals('active', $tournament->status);
        $this->assertEquals('UID on proof screenshot does not match the winner.', $tournament->admin_review_note);
    }
}
