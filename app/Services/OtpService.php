<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\OtpCode;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public const EXPIRES_IN = 300;
    private const RESEND_AFTER = 60;
    private const MAX_ATTEMPTS = 5;

    public function __construct(
        private SmsService $sms,
        private OneSignalService $oneSignal
    ) {
    }

    public function send(string $phone, ?string $playerId = null, ?int $userId = null): string
    {
        $existing = OtpCode::where('phone', $phone)->first();

        if ($existing && $existing->updated_at && $existing->updated_at->gt(now()->subSeconds(self::RESEND_AFTER))) {
            $wait = max(1, (int) ceil(self::RESEND_AFTER - abs(now()->diffInSeconds($existing->updated_at))));
            abort(429, "Please wait {$wait} seconds before requesting a new OTP.");
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::updateOrCreate(['phone' => $phone], [
            'code' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addSeconds(self::EXPIRES_IN),
        ]);

        // 1. Primary: Mobile Push Notification
        try {
            $this->oneSignal->sendOtpNotification($phone, $code, $playerId, $userId);
        } catch (\Throwable $e) {
            Log::warning('OneSignal OTP dispatch failed: ' . $e->getMessage());
        }

        // 2. In-App Notification record (so player can also see it in their notification inbox)
        if ($userId) {
            Notification::create([
                'user_id' => $userId,
                'target' => 'specific',
                'external_id' => (string) $userId,
                'title' => 'Verification Code (OTP)',
                'description' => "Your FF Tournament OTP is {$code}. Valid for 5 minutes. Do not share it.",
                'small_icon' => 'ic_stat_app_logo',
                'data' => ['type' => 'otp', 'otp' => $code],
                'status' => 'sent',
            ]);
        }

        // 3. Optional SMS fallback if admin enables SMS in settings
        $channel = Setting::get('otp_channel', 'notification');
        if ($channel === 'both' || $channel === 'sms') {
            try {
                $this->sms->send($phone, "Your FF Tournament OTP is {$code}. Valid for 5 minutes. Do not share it.");
            } catch (\Throwable $e) {
                Log::warning('SMS fallback failed: ' . $e->getMessage());
            }
        }

        return $code;
    }

    public function verify(string $phone, string $code): void
    {
        $otp = OtpCode::where('phone', $phone)->first();

        abort_if(! $otp, 422, 'Please request an OTP first.');

        if ($otp->expires_at->isPast()) {
            $otp->delete();
            abort(422, 'OTP has expired. Please request a new one.');
        }

        if ($otp->attempts >= self::MAX_ATTEMPTS) {
            $otp->delete();
            abort(429, 'Too many wrong attempts. Please request a new OTP.');
        }

        if (! Hash::check($code, $otp->code)) {
            $otp->increment('attempts');
            abort(422, 'Invalid OTP.');
        }

        $otp->delete();
    }
}
