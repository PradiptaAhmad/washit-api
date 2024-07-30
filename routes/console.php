<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('app:delete-expired-o-t-p')->everyFiveMinutes()->runInBackground();
Schedule::command('app:transfer-order-to-history-command')->dailyAt('00:00')->runInBackground();
