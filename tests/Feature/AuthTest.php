<?php

namespace Tests\Feature;

use App\Models\OtpCode;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $mockSms = Mockery::mock(SmsService::class);
        $mockSms->shouldReceive('send')->andReturn(true);
        $this->app->instance(SmsService::class, $mockSms);
    }

    public function test_send_otp_validation(): void
    {
        $response = $this->postJson('/api/send-otp', [
            'phone' => '01712345678',
            'purpose' => 'register',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'expires_in']);

        $this->assertDatabaseHas('otp_codes', ['phone' => '01712345678']);
    }

    public function test_resend_otp_api(): void
    {
        // First send
        $this->postJson('/api/send-otp', [
            'phone' => '01812345678',
            'purpose' => 'register',
        ]);

        // Immediate resend should be rate-limited (cooldown 60s)
        $response = $this->postJson('/api/resend-otp', [
            'phone' => '01812345678',
        ]);
        $response->assertStatus(429);

        // Fast forward 65 seconds
        $otp = OtpCode::where('phone', '01812345678')->first();
        $otp->updated_at = now()->subSeconds(65);
        $otp->save();

        // Now resend should succeed
        $response = $this->postJson('/api/resend-otp', [
            'phone' => '01812345678',
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'OTP resent to your phone.',
            ]);
    }

    public function test_register_flow_with_name_and_phone_then_otp_gives_token(): void
    {
        // Step 1: User provides name and phone -> sends OTP
        $step1 = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'phone' => '01912345678',
        ]);

        $step1->assertStatus(200)
            ->assertJson([
                'message' => 'OTP sent to your phone.',
            ]);

        // Inject fixed OTP in DB
        $code = '123456';
        OtpCode::where('phone', '01912345678')->update([
            'code' => Hash::make($code),
        ]);

        // Step 2: User provides OTP to register endpoint -> receives token
        $step2 = $this->postJson('/api/register', [
            'phone' => '01912345678',
            'otp' => '123456',
        ]);

        $step2->assertStatus(201)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'phone'],
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'phone' => '01912345678',
        ]);
    }

    public function test_register_flow_verify_otp_endpoint(): void
    {
        // Step 1: User registers with name and phone
        $this->postJson('/api/register', [
            'name' => 'Jane Smith',
            'phone' => '01799887766',
        ]);

        // Inject fixed OTP
        OtpCode::where('phone', '01799887766')->update([
            'code' => Hash::make('654321'),
        ]);

        // Step 2: Verifying via /api/verify-otp gives token
        $response = $this->postJson('/api/verify-otp', [
            'phone' => '01799887766',
            'otp' => '654321',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'phone'],
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Jane Smith',
            'phone' => '01799887766',
        ]);
    }

    public function test_duplicate_registration_fails(): void
    {
        User::create(['name' => 'Existing', 'phone' => '01711112233']);

        $response = $this->postJson('/api/register', [
            'name' => 'New Guy',
            'phone' => '01711112233',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_flow(): void
    {
        $user = User::create(['name' => 'Login Player', 'phone' => '01755554433']);

        // Request login OTP
        $this->postJson('/api/send-otp', [
            'phone' => '01755554433',
            'purpose' => 'login',
        ])->assertStatus(200);

        OtpCode::where('phone', '01755554433')->update([
            'code' => Hash::make('112233'),
        ]);

        // Login with OTP
        $response = $this->postJson('/api/login', [
            'phone' => '01755554433',
            'otp' => '112233',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'phone'],
            ]);
    }

    public function test_blocked_user_cannot_login(): void
    {
        User::create(['name' => 'Bad Guy', 'phone' => '01766667788', 'is_blocked' => true]);

        OtpCode::create([
            'phone' => '01766667788',
            'code' => Hash::make('123123'),
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/login', [
            'phone' => '01766667788',
            'otp' => '123123',
        ]);

        $response->assertStatus(403);
    }
}
