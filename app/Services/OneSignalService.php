<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    public function getAppId(): string
    {
        return (string) (Setting::get('onesignal_app_id') ?: config('services.onesignal.app_id'));
    }

    public function getRestApiKey(): string
    {
        return (string) (Setting::get('onesignal_rest_api_key') ?: config('services.onesignal.rest_api_key'));
    }

    public function getAndroidChannelId(): ?string
    {
        return Setting::get('onesignal_channel_id') ?: config('services.onesignal.android_channel_id');
    }

    public function getSmallIcon(): string
    {
        return (string) (Setting::get('onesignal_small_icon') ?: config('services.onesignal.small_icon', 'ic_stat_app_logo'));
    }

    /**
     * Broadcast notification to all subscribed users.
     */
    public function sendToAll(
        string $title,
        string $message,
        ?string $bigPicture = null,
        ?string $largeIcon = null,
        ?array $data = null
    ): array {
        $payload = [
            'app_id' => $this->getAppId(),
            'included_segments' => ['All'],
            'priority' => 10,
            'small_icon' => $this->getSmallIcon(),
            'channel_for_external_user_ids' => 'push',
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
        ];

        $channelId = $this->getAndroidChannelId();
        if ($channelId) {
            $payload['android_channel_id'] = $channelId;
        }

        if ($bigPicture) {
            $payload['big_picture'] = $bigPicture;
        }

        if ($largeIcon) {
            $payload['large_icon'] = $largeIcon;
        }

        if (!empty($data)) {
            $payload['data'] = $data;
        }

        return $this->send($payload);
    }

    /**
     * Send notification to a specific user using their external_id (User ID).
     */
    public function sendToUser(
        string|int $userId,
        string $title,
        string $message,
        ?string $bigPicture = null,
        ?string $largeIcon = null,
        ?array $data = null
    ): array {
        $payload = [
            'app_id' => $this->getAppId(),
            'include_aliases' => [
                'external_id' => [(string) $userId],
            ],
            'target_channel' => 'push',
            'priority' => 10,
            'small_icon' => $this->getSmallIcon(),
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
        ];

        $channelId = $this->getAndroidChannelId();
        if ($channelId) {
            $payload['android_channel_id'] = $channelId;
        }

        if ($bigPicture) {
            $payload['big_picture'] = $bigPicture;
        }

        if ($largeIcon) {
            $payload['large_icon'] = $largeIcon;
        }

        if (!empty($data)) {
            $payload['data'] = $data;
        }

        return $this->send($payload);
    }

    /**
     * Send notification to multiple users using external_ids (User IDs).
     */
    public function sendToUsers(
        array $userIds,
        string $title,
        string $message,
        ?string $bigPicture = null,
        ?string $largeIcon = null,
        ?array $data = null
    ): array {
        if (empty($userIds)) {
            return ['success' => true, 'id' => null, 'recipients' => 0];
        }

        $externalIds = array_values(array_unique(array_map('strval', $userIds)));

        $payload = [
            'app_id' => $this->getAppId(),
            'include_aliases' => [
                'external_id' => $externalIds,
            ],
            'target_channel' => 'push',
            'priority' => 10,
            'small_icon' => $this->getSmallIcon(),
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
        ];

        $channelId = $this->getAndroidChannelId();
        if ($channelId) {
            $payload['android_channel_id'] = $channelId;
        }

        if ($bigPicture) {
            $payload['big_picture'] = $bigPicture;
        }

        if ($largeIcon) {
            $payload['large_icon'] = $largeIcon;
        }

        if (!empty($data)) {
            $payload['data'] = $data;
        }

        return $this->send($payload);
    }

    /**
     * Send OTP verification via push notification.
     */
    public function sendOtpNotification(
        string $phone,
        string $code,
        ?string $playerId = null,
        ?int $userId = null
    ): array {
        $title = 'FF Tournament OTP Code';
        $message = "Your FF Tournament OTP is {$code}. Valid for 5 minutes. Do not share it with anyone.";
        $data = ['type' => 'otp', 'otp' => $code, 'phone' => $phone];

        $payload = [
            'app_id' => $this->getAppId(),
            'priority' => 10,
            'small_icon' => $this->getSmallIcon(),
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
            'data' => $data,
        ];

        $channelId = $this->getAndroidChannelId();
        if ($channelId) {
            $payload['android_channel_id'] = $channelId;
        }

        if (!empty($playerId)) {
            $payload['include_player_ids'] = [$playerId];
        } elseif (!empty($userId)) {
            $payload['include_aliases'] = ['external_id' => [(string) $userId]];
            $payload['target_channel'] = 'push';
        } else {
            $payload['include_aliases'] = ['external_id' => [$phone]];
            $payload['target_channel'] = 'push';
        }

        return $this->send($payload);
    }

    /**
     * Send Room ID and Password notification to all joined tournament players.
     */
    public function sendRoomDetailsNotification(
        array $userIds,
        string $matchNumber,
        string $roomId,
        string $roomPassword,
        int $tournamentId
    ): array {
        $title = "Room Details - {$matchNumber}";
        $message = "Room ID: {$roomId} | Password: {$roomPassword}. Match starting soon! Please join quickly.";
        $data = [
            'type' => 'tournament',
            'tournament_id' => (string) $tournamentId,
            'room_id' => $roomId,
            'room_password' => $roomPassword,
        ];

        return $this->sendToUsers($userIds, $title, $message, null, null, $data);
    }

    /**
     * Send Match Results & Prizes publication notification to all joined tournament players.
     */
    public function sendMatchResultsNotification(
        array $userIds,
        string $matchNumber,
        int $tournamentId
    ): array {
        $title = "Match Results & Prizes Published! ({$matchNumber})";
        $message = "Results for {$matchNumber} have been published and prizes have been credited to player wallets!";
        $data = [
            'type' => 'tournament',
            'tournament_id' => (string) $tournamentId,
        ];

        return $this->sendToUsers($userIds, $title, $message, null, null, $data);
    }

    /**
     * Send Commission notification to the tournament moderator.
     */
    public function sendCommissionNotification(
        int $moderatorId,
        string $matchNumber,
        int $amount,
        int $tournamentId
    ): array {
        $title = "Commission Received! +{$amount} BDT";
        $message = "You earned {$amount} BDT commission for successfully arranging and completing match {$matchNumber}.";
        $data = [
            'type' => 'commission',
            'tournament_id' => (string) $tournamentId,
            'amount' => $amount,
        ];

        return $this->sendToUser($moderatorId, $title, $message, null, null, $data);
    }

    /**
     * Dispatch notification payload to OneSignal REST API.
     */
    public function send(array $payload): array
    {
        $apiKey = $this->getRestApiKey();
        $authHeader = str_starts_with(trim($apiKey), 'Basic ') ? trim($apiKey) : 'Basic ' . trim($apiKey);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => $authHeader,
            ])
            ->timeout(15)
            ->post('https://api.onesignal.com/notifications', $payload);

            $body = $response->json();

            if ($response->successful() && !empty($body['id'])) {
                return [
                    'success' => true,
                    'id' => $body['id'],
                    'recipients' => (int) ($body['recipients'] ?? 0),
                    'warnings' => $body['warnings'] ?? null,
                    'errors' => $body['errors'] ?? null,
                    'raw' => $body,
                ];
            }

            $errorMessage = null;
            if (!empty($body['errors'])) {
                $errorMessage = is_array($body['errors']) ? implode(', ', $body['errors']) : (string) $body['errors'];
            } else {
                $errorMessage = 'HTTP ' . $response->status() . ': ' . $response->body();
            }

            Log::warning('OneSignal notification error', [
                'status' => $response->status(),
                'response' => $body,
            ]);

            return [
                'success' => false,
                'id' => $body['id'] ?? null,
                'recipients' => (int) ($body['recipients'] ?? 0),
                'error' => $errorMessage,
                'raw' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('OneSignal request failed', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'id' => null,
                'recipients' => 0,
                'error' => $e->getMessage(),
                'raw' => null,
            ];
        }
    }
}
