<?php

return [
    'driver' => env('SMS_DRIVER', 'log'),   // log | bulksmsbd
    'url' => env('SMS_API_URL', 'http://bulksmsbd.net/api/smsapi'),
    'api_key' => env('SMS_API_KEY'),
    'sender_id' => env('SMS_SENDER_ID'),
];
