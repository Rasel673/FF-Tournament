<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin panel login: admin@example.com / password  (change after first login!)
        User::updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Admin',
            'password' => 'password',
            'is_admin' => true,
        ]);

        // Demo player for testing the app API: phone 01700000000 / password
        User::updateOrCreate(['phone' => '01700000000'], [
            'name' => 'Jane Cooper',
            'uid' => '123456789',
            'password' => 'password',
            'balance' => 550,
        ]);

        foreach (['bKash', 'Nagad', 'Upay', 'Rocket'] as $name) {
            PaymentMethod::firstOrCreate(['name' => $name], ['account_number' => '01XXXXXXXXX']);
        }

        foreach ([
            'support_phone' => '', 'support_whatsapp' => '', 'support_telegram' => '',
            'support_email' => '', 'min_deposit' => '10', 'min_withdraw' => '50',
        ] as $key => $value) {
            if (Setting::where('key', $key)->doesntExist()) {
                Setting::put($key, $value);
            }
        }

        if (Tournament::count() === 0) {
            Tournament::create([
                'title' => 'BR Rank Push', 'map' => 'Bermuda', 'entry_fee' => 50, 'prize' => 600,
                'per_kill' => 10, 'slots' => 50, 'start_time' => now()->setTime(20, 0),
                'status' => 'active', 'room_id' => '12345678', 'room_password' => 'ff1234',
            ]);
            Tournament::create([
                'title' => 'CS Clash Squad', 'map' => 'Kalahari', 'entry_fee' => 30, 'prize' => 300,
                'per_kill' => 5, 'slots' => 50, 'start_time' => now()->setTime(22, 0),
                'open_time' => now()->setTime(21, 0), 'status' => 'upcoming',
            ]);
            Tournament::create([
                'title' => 'BR Custom', 'map' => 'Alpine', 'entry_fee' => 50, 'prize' => 600,
                'per_kill' => 10, 'slots' => 50, 'start_time' => now()->subDay()->setTime(20, 0),
                'status' => 'completed',
            ]);
        }
    }
}
