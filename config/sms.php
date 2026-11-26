<?php

return [
    'driver' => env('SMS_DRIVER', 'globalitbd'),   // log | bulksmsbd | globalitbd
    'url' => env('SMS_API_URL', 'https://sms.globalitbd.net/send-message'),
    'client_id' => env('SMS_CLIENT_ID', '000031'),
    'client_secret' => env('SMS_CLIENT_SECRET', 'b30a-cfb7-4884-8174-e2ac3767c5a4'),
    'sender_id' => env('SMS_SENDER_ID', 'SENDER_ID'),
    'api_key' => env('SMS_API_KEY'),
];

