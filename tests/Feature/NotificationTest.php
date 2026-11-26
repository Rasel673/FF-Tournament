<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NotificationTest extends TestCase
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
            'uid' => (string) random_int(100000000, 999999999),
        ]);
    }

    public function test_guest_cannot_access_notifications(): void
    {
        $response = $this->get('/admin/notifications');
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_notifications_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/notifications');
        $response->assertStatus(200)
            ->assertSee('Push Notifications')
            ->assertSee('Compose Notification')
            ->assertSee('All Users')
            ->assertSee('Specific User');
    }

    public function test_admin_can_send_notification_to_all_users(): void
    {
        $admin = $this->createAdmin();

        Http::fake([
            'https://api.onesignal.com/notifications' => Http::response([
                'id' => 'os_mock_uuid_123',
                'recipients' => 50,
            ], 200),
        ]);

        $payload = [
            'target' => 'all',
            'title' => 'Broadcast Title',
            'message' => 'Broadcast message body text',
            'big_picture_url' => 'https://example.com/banner.jpg',
            'large_icon_url' => 'https://example.com/icon.jpg',
            'data_type' => 'wallet',
        ];

        $response = $this->actingAs($admin)->post('/admin/notifications', $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $data['included_segments'] === ['All']
                && $data['headings']['en'] === 'Broadcast Title'
                && $data['contents']['en'] === 'Broadcast message body text'
                && $data['big_picture'] === 'https://example.com/banner.jpg'
                && $data['large_icon'] === 'https://example.com/icon.jpg'
                && $data['data']['type'] === 'wallet';
        });

        $this->assertDatabaseHas('notifications', [
            'target' => 'all',
            'user_id' => null,
            'title' => 'Broadcast Title',
            'description' => 'Broadcast message body text',
            'onesignal_id' => 'os_mock_uuid_123',
            'recipients_count' => 50,
            'status' => 'sent',
        ]);
    }

    public function test_admin_can_send_notification_to_specific_user(): void
    {
        $admin = $this->createAdmin();
        $player = $this->createPlayer('01833025822', 'Mokarom');

        $tournament = Tournament::create([
            'title' => 'BR SOLO MATCH',
            'match_number' => 'No.206513',
            'map' => 'Bermuda',
            'entry_fee' => 10,
            'prize' => 390,
            'slots' => 48,
            'start_time' => now()->addHours(2),
            'status' => 'active',
        ]);

        Http::fake([
            'https://api.onesignal.com/notifications' => Http::response([
                'id' => 'os_mock_user_uuid_456',
                'recipients' => 1,
            ], 200),
        ]);

        $payload = [
            'target' => 'specific',
            'user_id' => $player->id,
            'title' => 'ম্যাচ শুরু হচ্ছে',
            'message' => 'BR Rank Push ১০ মিনিটের মধ্যে শুরু হবে। Room ID দেখে নিন।',
            'data_type' => 'tournament',
            'tournament_id' => $tournament->id,
        ];

        $response = $this->actingAs($admin)->post('/admin/notifications', $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) use ($player, $tournament) {
            $data = $request->data();
            return isset($data['include_aliases']['external_id'])
                && in_array((string) $player->id, $data['include_aliases']['external_id'])
                && $data['headings']['en'] === 'ম্যাচ শুরু হচ্ছে'
                && $data['data']['tournament_id'] === (string) $tournament->id;
        });

        $this->assertDatabaseHas('notifications', [
            'target' => 'specific',
            'user_id' => $player->id,
            'external_id' => (string) $player->id,
            'title' => 'ম্যাচ শুরু হচ্ছে',
            'onesignal_id' => 'os_mock_user_uuid_456',
            'recipients_count' => 1,
            'status' => 'sent',
        ]);
    }

    public function test_admin_can_delete_notification(): void
    {
        $admin = $this->createAdmin();
        $notification = Notification::create([
            'target' => 'all',
            'title' => 'Old Title',
            'description' => 'Old Desc',
            'status' => 'sent',
        ]);

        $response = $this->actingAs($admin)->delete("/admin/notifications/{$notification->id}");
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_api_returns_public_and_user_notifications(): void
    {
        $player1 = $this->createPlayer('01711111111', 'Player 1');
        $player2 = $this->createPlayer('01722222222', 'Player 2');

        // Broadcast to all
        Notification::create([
            'target' => 'all',
            'title' => 'Public News',
            'description' => 'Everyone sees this',
            'small_icon' => 'https://example.com/icon.png',
        ]);

        // Specific to player 1
        Notification::create([
            'target' => 'specific',
            'user_id' => $player1->id,
            'title' => 'Personal Message to Player 1',
            'description' => 'Private',
        ]);

        // Specific to player 2
        Notification::create([
            'target' => 'specific',
            'user_id' => $player2->id,
            'title' => 'Personal Message to Player 2',
            'description' => 'Private',
        ]);

        // Guest sees only public
        $guestResponse = $this->getJson('/api/notifications');
        $guestResponse->assertStatus(200)
            ->assertJsonFragment(['title' => 'Public News', 'small_icon' => 'https://example.com/icon.png'])
            ->assertJsonMissing(['title' => 'Personal Message to Player 1'])
            ->assertJsonMissing(['title' => 'Personal Message to Player 2']);

        // Player 1 sees public + player 1
        $player1Response = $this->actingAs($player1, 'sanctum')->getJson('/api/notifications');
        $player1Response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Public News'])
            ->assertJsonFragment(['title' => 'Personal Message to Player 1'])
            ->assertJsonMissing(['title' => 'Personal Message to Player 2']);
    }
}
