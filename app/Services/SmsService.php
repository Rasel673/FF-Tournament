<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SmsService
{
    public function send(string $phone, string $message): bool
    {
        return match (config('sms.driver')) {
            'log' => $this->viaLog($phone, $message),
            'bulksmsbd' => $this->viaBulkSmsBd($phone, $message),
            default => throw new InvalidArgumentException('Unknown SMS_DRIVER: '.config('sms.driver')),
        };
    }

    private function viaLog(string $phone, string $message): bool
    {
        Log::info("SMS to {$phone}: {$message}");

        return true;
    }

    private function viaBulkSmsBd(string $phone, string $message): bool
    {
        try {
            $response = Http::timeout(10)->asForm()->post(config('sms.url'), [
                'api_key' => config('sms.api_key'),
                'type' => 'text',
                'number' => '88'.$phone,
                'senderid' => config('sms.sender_id'),
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        return $response->successful() && (int) $response->json('response_code') === 202;
    }
}
