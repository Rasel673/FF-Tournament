<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerTest extends TestCase
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

    public function test_guest_cannot_access_admin_banners(): void
    {
        $response = $this->get('/admin/banners');
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_banners_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/banners');
        $response->assertStatus(200);
        $response->assertSee('Pictures & Banners');
        $response->assertSee('/api/banners');
    }

    public function test_admin_can_upload_multiple_pictures(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        // Upload first photo
        $file1 = UploadedFile::fake()->create('promo1.jpg', 100, 'image/jpeg');
        $response1 = $this->actingAs($admin)->post('/admin/banners', [
            'image' => $file1,
            'title' => 'Championship Banner',
            'link_url' => 'https://example.com/tournaments/1',
            'order' => 1,
            'is_active' => '1',
        ]);
        $response1->assertRedirect();
        $response1->assertSessionHas('success');

        // Upload second photo
        $file2 = UploadedFile::fake()->create('promo2.png', 120, 'image/png');
        $response2 = $this->actingAs($admin)->post('/admin/banners', [
            'image' => $file2,
            'title' => 'Special Event Banner',
            'link_url' => 'https://example.com/tournaments/2',
            'order' => 2,
            'is_active' => '1',
        ]);
        $response2->assertRedirect();
        $response2->assertSessionHas('success');

        // Both photos must exist in database!
        $this->assertEquals(2, Banner::count());
        $banner1 = Banner::where('title', 'Championship Banner')->first();
        $banner2 = Banner::where('title', 'Special Event Banner')->first();

        Storage::disk('public')->assertExists($banner1->image_path);
        Storage::disk('public')->assertExists($banner2->image_path);
    }

    public function test_api_returns_all_active_pictures(): void
    {
        Storage::fake('public');

        Banner::create([
            'title' => 'Active Banner 1',
            'image_path' => 'banners/banner1.jpg',
            'link_url' => 'https://example.com/promo1',
            'is_active' => true,
            'order' => 1,
        ]);

        Banner::create([
            'title' => 'Active Banner 2',
            'image_path' => 'banners/banner2.jpg',
            'link_url' => 'https://example.com/promo2',
            'is_active' => true,
            'order' => 2,
        ]);

        Banner::create([
            'title' => 'Inactive Banner',
            'image_path' => 'banners/banner3.jpg',
            'is_active' => false,
            'order' => 3,
        ]);

        $response = $this->getJson('/api/banners');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'title' => 'Active Banner 1',
            ])
            ->assertJsonFragment([
                'title' => 'Active Banner 2',
            ])
            ->assertJsonMissing([
                'title' => 'Inactive Banner',
            ]);

        // Pictures alias works too
        $picResponse = $this->getJson('/api/pictures');
        $picResponse->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_toggle_and_delete_specific_picture(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $file1 = UploadedFile::fake()->create('img1.jpg', 50, 'image/jpeg');
        $path1 = $file1->store('banners', 'public');
        $banner1 = Banner::create([
            'title' => 'Image 1',
            'image_path' => $path1,
            'is_active' => true,
        ]);

        $file2 = UploadedFile::fake()->create('img2.jpg', 50, 'image/jpeg');
        $path2 = $file2->store('banners', 'public');
        $banner2 = Banner::create([
            'title' => 'Image 2',
            'image_path' => $path2,
            'is_active' => true,
        ]);

        // Toggle banner1
        $this->actingAs($admin)->post("/admin/banners/{$banner1->id}/toggle");
        $this->assertFalse($banner1->fresh()->is_active);
        $this->assertTrue($banner2->fresh()->is_active);

        // Delete banner1
        $this->actingAs($admin)->delete("/admin/banners/{$banner1->id}");

        $this->assertDatabaseMissing('banners', ['id' => $banner1->id]);
        $this->assertDatabaseHas('banners', ['id' => $banner2->id]);

        Storage::disk('public')->assertMissing($path1);
        Storage::disk('public')->assertExists($path2);
    }

    public function test_can_upload_picture_via_api(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('api-photo.jpg', 80, 'image/jpeg');

        $response = $this->postJson('/api/banners', [
            'image' => $file,
            'title' => 'API Uploaded Banner',
            'link_url' => 'https://example.com',
            'order' => 5,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Picture uploaded successfully',
            ])
            ->assertJsonFragment([
                'title' => 'API Uploaded Banner',
            ]);

        $this->assertDatabaseHas('banners', [
            'title' => 'API Uploaded Banner',
        ]);
    }
}
