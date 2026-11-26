<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryTest extends TestCase
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

    private function createPlayer(): User
    {
        return User::create([
            'name' => 'Player 1',
            'phone' => '01800000001',
            'password' => bcrypt('password'),
            'balance' => 500,
        ]);
    }

    public function test_admin_can_access_categories_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/categories');
        $response->assertStatus(200);
        $response->assertSee('Tournament Categories');
        $response->assertSee('/api/categories');
    }

    public function test_admin_can_create_category_with_image_title_description(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $file = UploadedFile::fake()->create('cs_banner.png', 100, 'image/png');

        $response = $this->actingAs($admin)->post('/admin/categories', [
            'title' => 'Clash Squad 4v4',
            'short_description' => 'Fast-paced 4v4 tactical rounds on Bermuda',
            'image' => $file,
            'order' => 1,
            'is_active' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'title' => 'Clash Squad 4v4',
            'short_description' => 'Fast-paced 4v4 tactical rounds on Bermuda',
            'order' => 1,
            'is_active' => true,
        ]);

        $category = Category::first();
        Storage::disk('public')->assertExists($category->image_path);
    }

    public function test_tournament_can_be_assigned_under_category(): void
    {
        $admin = $this->createAdmin();

        $category = Category::create([
            'title' => 'Battle Royale',
            'short_description' => '50-player classic survival',
        ]);

        $response = $this->actingAs($admin)->post('/admin/tournaments', [
            'category_id' => $category->id,
            'title' => 'BR Rank Push',
            'map' => 'Bermuda',
            'type' => 'Solo',
            'entry_fee' => 50,
            'prize' => 500,
            'per_kill' => 10,
            'slots' => 50,
            'start_time' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'status' => 'upcoming',
        ]);

        $response->assertRedirect('/admin/tournaments');

        $this->assertDatabaseHas('tournaments', [
            'title' => 'BR Rank Push',
            'category_id' => $category->id,
        ]);

        $tournament = Tournament::first();
        $this->assertEquals($category->id, $tournament->category->id);
        $this->assertEquals(1, $category->tournaments()->count());
    }

    public function test_api_categories_returns_active_categories(): void
    {
        $cat1 = Category::create([
            'title' => 'Clash Squad',
            'image_path' => 'categories/cs.jpg',
            'short_description' => '4v4 custom rounds',
            'order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'title' => 'Secret Inactive Category',
            'is_active' => false,
            'order' => 2,
        ]);

        // Create a tournament under cat1
        Tournament::create([
            'category_id' => $cat1->id,
            'title' => 'CS Championship',
            'map' => 'Kalahari',
            'entry_fee' => 30,
            'prize' => 300,
            'slots' => 8,
            'start_time' => now()->addDay(),
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'title' => 'Clash Squad',
                'short_description' => '4v4 custom rounds',
                'tournaments_count' => 1,
            ])
            ->assertJsonMissing([
                'title' => 'Secret Inactive Category',
            ]);
    }

    public function test_api_category_tournaments_endpoint(): void
    {
        $player = $this->createPlayer();

        $cat = Category::create([
            'title' => 'Lone Wolf',
            'short_description' => '1v1 duels',
            'is_active' => true,
        ]);

        $tournament = Tournament::create([
            'category_id' => $cat->id,
            'title' => 'Lone Wolf 1v1 Round 1',
            'map' => 'Iron Cage',
            'entry_fee' => 20,
            'prize' => 35,
            'slots' => 2,
            'start_time' => now()->addHours(3),
            'status' => 'active',
        ]);

        $response = $this->actingAs($player, 'sanctum')->getJson("/api/categories/{$cat->id}/tournaments");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'Lone Wolf 1v1 Round 1',
                'category_id' => $cat->id,
            ]);
    }

    public function test_api_tournaments_can_filter_by_category(): void
    {
        $player = $this->createPlayer();

        $cat1 = Category::create(['title' => 'BR', 'is_active' => true]);
        $cat2 = Category::create(['title' => 'CS', 'is_active' => true]);

        Tournament::create([
            'category_id' => $cat1->id,
            'title' => 'BR Match',
            'map' => 'Bermuda',
            'entry_fee' => 10,
            'prize' => 50,
            'slots' => 48,
            'start_time' => now()->addHours(2),
            'status' => 'active',
        ]);

        Tournament::create([
            'category_id' => $cat2->id,
            'title' => 'CS Match',
            'map' => 'Bermuda',
            'entry_fee' => 20,
            'prize' => 70,
            'slots' => 8,
            'start_time' => now()->addHours(3),
            'status' => 'active',
        ]);

        $response = $this->actingAs($player, 'sanctum')->getJson("/api/tournaments?category_id={$cat1->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'BR Match'])
            ->assertJsonMissing(['title' => 'CS Match']);
    }

    public function test_home_api_includes_categories(): void
    {
        $player = $this->createPlayer();

        Category::create([
            'title' => 'Home Category',
            'is_active' => true,
        ]);

        $response = $this->actingAs($player, 'sanctum')->getJson('/api/home');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'banners',
                    'categories',
                    'tournaments',
                ],
            ])
            ->assertJsonFragment(['title' => 'Home Category']);
    }

    public function test_admin_can_toggle_and_delete_category(): void
    {
        $admin = $this->createAdmin();

        $category = Category::create([
            'title' => 'To be deleted',
            'is_active' => true,
        ]);

        // Toggle
        $this->actingAs($admin)->post("/admin/categories/{$category->id}/toggle");
        $this->assertFalse($category->fresh()->is_active);

        // Delete
        $this->actingAs($admin)->delete("/admin/categories/{$category->id}");
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_tournament_app_ui_fields_and_prize_pool(): void
    {
        $admin = $this->createAdmin();
        $player = $this->createPlayer();

        $cat = Category::create([
            'title' => 'PRO LEAGUE',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post('/admin/tournaments', [
            'match_number' => 'No.206513',
            'category_id' => $cat->id,
            'title' => 'PRO LEAGUE MATCH',
            'map' => 'Bermuda',
            'type' => 'Squad',
            'entry_fee' => 10,
            'prize' => 390,
            'per_kill' => 0,
            'slots' => 48,
            'start_time' => now()->addHours(6)->addMinutes(30)->format('Y-m-d H:i:s'),
            'status' => 'active',
            'tutorial_video_url' => 'https://youtube.com/watch?v=example',
            'rules_title' => 'SQUAD SCRIM MATCH RULES',
            'prize_positions' => ['Winner', '2nd Position', '3rd Position', '4th Position'],
            'prize_amounts' => [160, 110, 80, 40],
        ]);

        $response->assertRedirect('/admin/tournaments');

        $tournament = Tournament::where('match_number', 'No.206513')->first();
        $this->assertNotNull($tournament);
        $this->assertEquals('Squad', $tournament->type);
        $this->assertEquals(4, count($tournament->prize_pool_distribution));

        // Test API response has UI fields
        $apiResponse = $this->actingAs($player, 'sanctum')->getJson("/api/tournaments/{$tournament->id}");
        $apiResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'match_number' => 'No.206513',
                    'type' => 'Squad',
                    'type_label' => 'Type: Squad',
                    'map_label' => 'Map: Bermuda',
                    'entry_fee_label' => 'Entry: 10 BDT',
                    'winning_prize' => 390,
                    'total_prize_pool_label' => '390 Taka',
                    'rules_title' => 'SQUAD SCRIM MATCH RULES',
                    'tutorial_video_url' => 'https://youtube.com/watch?v=example',
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'slots_label',
                    'players_needed_label',
                    'spots_available_label',
                    'starts_in' => ['hours', 'minutes', 'seconds', 'formatted'],
                    'prize_pool_distribution',
                    'rules',
                ],
            ]);
    }

    public function test_deposit_api_with_id_and_trx_aliases(): void
    {
        $player = $this->createPlayer();
        $pm = PaymentMethod::create([
            'name' => 'bKash',
            'account_number' => '01833025822',
            'is_active' => true,
        ]);

        $payload = [
            'method' => $pm->id,
            'amount' => 100,
            'sender_number' => '01833025822',
            'trx' => 'TRX987654321',
            'screenshot' => '',
        ];

        $response = $this->actingAs($player, 'sanctum')->postJson('/api/wallet/deposit', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'deposit')
            ->assertJsonPath('data.method', 'bKash');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $player->id,
            'type' => 'deposit',
            'amount' => 100,
            'method' => 'bKash',
            'trx_id' => 'TRX987654321',
            'account_number' => '01833025822',
            'status' => 'pending',
        ]);
    }
}

