<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sms:test {phone : Recipient mobile number (e.g. 017XXXXXXXX)} {message? : Custom test message}', function (string $phone, ?string $message = null) {
    /** @var \App\Services\SmsService $sms */
    $sms = app(\App\Services\SmsService::class);
    $driver = config('sms.driver');
    $msg = $message ?? 'Test SMS from FF Tournament at ' . now()->toDateTimeString();

    $this->info("Sending test SMS to {$phone} using driver [{$driver}]...");
    $success = $sms->send($phone, $msg);

    if ($success) {
        $this->info('SMS sent successfully!');
        if ($sms->getLastResponse()) {
            $this->comment('Gateway response: ' . $sms->getLastResponse());
        }
    } else {
        $this->error('Failed to send SMS.');
        if ($sms->getLastResponse()) {
            $this->warn('Gateway response: ' . $sms->getLastResponse());
        }
        $this->line('Check storage/logs/laravel.log for full details.');
    }
})->purpose('Send a test SMS through the configured gateway');

