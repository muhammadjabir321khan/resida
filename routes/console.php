<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Production: add cron `* * * * * php artisan schedule:run` and configure mail (.env).
Schedule::command('rental:automate-statuses')->dailyAt('06:00');
Schedule::command('rental:send-landlord-digests')->dailyAt('07:00');
Schedule::command('rental:send-rent-reminders')->dailyAt('08:00');
