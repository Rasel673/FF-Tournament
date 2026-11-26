<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SmsService
{
    private ?string $lastResponse = null;

    public function getLastResponse(): ?string
    {
        return $this->lastResponse;
    }

    public function send(string $phone, string $message): bool
    {
        $this->lastResponse = null;

        return match (config('sms.driver')) {
            'log' => $this->viaLog($phone, $message),
            'bulksmsbd' => $this->viaBulkSmsBd($phone, $message),
            'globalitbd' => $this->viaGlobalItBd($phone, $message),
            default => throw new InvalidArgumentException('Unknown SMS_DRIVER: '.config('sms.driver')),
        };
    }

    private function viaLog(string $phone, string $message): bool
    {
        Log::info("SMS to {$phone}: {$message}");
        $this->lastResponse = "Logged locally: SMS to {$phone}: {$message}";

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
            $this->lastResponse = $response->body();
        } catch (\Throwable $e) {
            report($e);
            $this->lastResponse = $e->getMessage();

            return false;
        }

        return $response->successful() && (int) $response->json('response_code') === 202;
    }

    private function viaGlobalItBd(string $phone, string $message): bool
    {
        try {
            // Strip any non-digit characters
            $cleanNumber = preg_replace('/\D+/', '', $phone);

            // Ensure 88 country code prefix if not present (e.g. 88017XXXXXXXX)
            if (! str_starts_with($cleanNumber, '88')) {
                $cleanNumber = '88' . $cleanNumber;
            }

            $url = config('sms.url') ?: 'https://sms.globalitbd.net/send-message';
            $clientId = config('sms.client_id');
            $clientSecret = config('sms.client_secret') ?: config('sms.api_key');
            $senderId = config('sms.sender_id');

            $payload = [
                'clientId' => (string) $clientId,
                'clientSecret' => (string) $clientSecret,
                'senderId' => (string) $senderId,
                'numbers' => $cleanNumber,
                'contents' => $message,
            ];

            // Primary: JSON POST request
            $response = Http::timeout(10)->asJson()->post($url, $payload);

            // If POST fails or is not supported by endpoint, fallback to GET query
            if (! $response->successful()) {
                $response = Http::timeout(10)->get($url, $payload);
            }

            $this->lastResponse = $response->body();
            Log::info("GlobalIT BD SMS response [{$cleanNumber}]: status={$response->status()} body={$response->body()}");

            if ($response->successful()) {
                $data = $response->json();

                if (is_array($data)) {
                    if (($data['success'] ?? false) === true) {
                        return true;
                    }

                    $status = strtolower((string) ($data['status'] ?? ''));
                    $responseCode = (int) ($data['response_code'] ?? $data['status_code'] ?? $data['code'] ?? 0);
                    $success = $data['success'] ?? null;

                    // Check for explicit error responses
                    if ($status === 'error' || $status === 'failed' || $success === false) {
                        Log::warning("GlobalIT BD SMS error response: {$response->body()}");

                        return false;
                    }

                    if (
                        $status === 'success' ||
                        in_array($responseCode, [200, 202, 1000], true) ||
                        ! empty($data['data']['message_id'] ?? null) ||
                        ! empty($data['message_id'] ?? null)
                    ) {
                        return true;
                    }
                }

                $body = strtolower($response->body());
                if (str_contains($body, 'success') || str_contains($body, 'submitted') || str_contains($body, '1000')) {
                    return true;
                }
            }

            Log::warning("GlobalIT BD SMS sending failed: {$response->body()}");

            return false;
        } catch (\Throwable $e) {
            report($e);
            $this->lastResponse = $e->getMessage();

            return false;
        }
    }
}

