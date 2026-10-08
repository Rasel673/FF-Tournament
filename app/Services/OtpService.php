<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    public const EXPIRES_IN = 300;
    private const RESEND_AFTER = 60;
    private const MAX_ATTEMPTS = 5;

    public function __construct(private SmsService $sms)
    {
    }

    public function send(string $phone): void
    {
        $existing = OtpCode::where('phone', $phone)->first();

        if ($existing && $existing->updated_at->gt(now()->subSeconds(self::RESEND_AFTER))) {
            $wait = (int) ceil(self::RESEND_AFTER - abs(now()->diffInSeconds($existing->updated_at)));
            abort(429, "Please wait {$wait} seconds before requesting a new OTP.");
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::updateOrCreate(['phone' => $phone], [
            'code' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addSeconds(self::EXPIRES_IN),
        ]);

        $sent = $this->sms->send($phone, "Your FF Tournament OTP is {$code}. Valid for 5 minutes. Do not share it.");

        if (! $sent) {
            OtpCode::where('phone', $phone)->delete();
            abort(503, 'Could not send the SMS. Please try again.');
        }
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
