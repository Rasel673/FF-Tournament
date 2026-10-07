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
    ];

    public function edit()
    {
        $values = [];
        foreach (self::FIELDS as $key => $label) {
            $values[$key] = Setting::get($key, '');
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
