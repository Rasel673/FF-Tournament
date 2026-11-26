<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Notification;
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

        foreach ([
            [
                'title' => 'Welcome to FF Tournament',
                'description' => 'Join exciting tournaments and compete with players from the community.',
                'large_image' => 'https://placehold.co/1200x500/png?text=FF+Tournament',
                'small_image' => 'https://placehold.co/200x200/png?text=FF',
            ],
            [
                'title' => 'New tournaments are available',
                'description' => 'Check the tournament list and reserve your slot before registration closes.',
                'large_image' => 'https://placehold.co/1200x500/png?text=New+Tournaments',
                'small_image' => 'https://placehold.co/200x200/png?text=New',
            ],
            [
                'title' => 'Play fair and have fun',
                'description' => null,
                'large_image' => null,
                'small_image' => 'https://placehold.co/200x200/png?text=Play',
            ],
        ] as $notification) {
            Notification::firstOrCreate(
                ['title' => $notification['title']],
                $notification
            );
        }

        $modes = [
            ['title' => 'PRO LEAGUE', 'short_description' => 'Official Pro League Competitive matches', 'order' => 1],
            ['title' => 'SURVIVAL', 'short_description' => 'Classic Survival Battle Royale', 'order' => 2],
            ['title' => 'CS 4 VS 4', 'short_description' => 'Fast-paced Clash Squad 4v4 tactical rounds', 'order' => 3],
            ['title' => 'CLASH SQUAD', 'short_description' => 'Classic Squad Battle mode', 'order' => 4],
            ['title' => 'LONE WOLF', 'short_description' => '1v1 Solo duels on Iron Cage', 'order' => 5],
            ['title' => 'CS HEADSHOT', 'short_description' => 'Headshot-only custom scrims', 'order' => 6],
            ['title' => 'LOSE 2 WIN', 'short_description' => 'Special challenge format matches', 'order' => 7],
            ['title' => 'FREE MATCH', 'short_description' => 'Zero entry fee free practice matches', 'order' => 8],
        ];

        foreach ($modes as $mode) {
            Category::firstOrCreate(['title' => $mode['title']], $mode);
        }

        $defaultCat = Category::first();
        if ($defaultCat) {
            Tournament::whereNull('category_id')->update(['category_id' => $defaultCat->id]);
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
