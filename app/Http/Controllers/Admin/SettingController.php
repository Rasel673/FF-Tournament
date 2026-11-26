<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private const FIELDS = [
        'support_phone' => 'Support phone',
        'support_whatsapp' => 'Support WhatsApp',
        'support_telegram' => 'Support Telegram',
        'support_email' => 'Support email',
        'min_deposit' => 'Minimum deposit (BDT)',
        'min_withdraw' => 'Minimum withdraw (BDT)',
        'onesignal_app_id' => 'OneSignal App ID',
        'onesignal_rest_api_key' => 'OneSignal REST API Key',
        'onesignal_channel_id' => 'OneSignal Android Channel ID',
        'default_moderator_commission' => 'Default Moderator Commission (BDT)',
        'min_tournaments_for_moderator' => 'Min Tournaments Played to Apply for Moderator',
        'otp_channel' => 'OTP Delivery Channel (notification, sms, both)',
    ];

    public function edit()
    {
        $values = [];
        foreach (self::FIELDS as $key => $label) {
            $default = match ($key) {
                'onesignal_app_id' => config('services.onesignal.app_id', ''),
                'onesignal_rest_api_key' => config('services.onesignal.rest_api_key', ''),
                'onesignal_channel_id' => config('services.onesignal.android_channel_id', ''),
                'default_moderator_commission' => '50',
                'min_tournaments_for_moderator' => '5',
                'otp_channel' => 'notification',
                default => '',
            };
            $values[$key] = Setting::get($key, $default);
        }

        return view('admin.settings', ['fields' => self::FIELDS, 'values' => $values]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'min_deposit' => 'required|integer|min:1',
            'min_withdraw' => 'required|integer|min:1',
        ]);

        foreach (array_keys(self::FIELDS) as $key) {
            Setting::put($key, $request->input($key));
        }

        return back()->with('success', 'Settings saved.');
    }
}
