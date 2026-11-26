<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class SupportController extends Controller
{
    /** Profile > Support. Contact details are managed from the admin panel (Settings). */
    public function index()
    {
        return response()->json([
            'data' => [
                'phone' => Setting::get('support_phone'),
                'whatsapp' => Setting::get('support_whatsapp'),
                'telegram' => Setting::get('support_telegram'),
                'email' => Setting::get('support_email'),
            ],
        ]);
    }
}
