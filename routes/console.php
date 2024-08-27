<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('app:delete-expired-o-t-p')->everyTwoMinutes();
Schedule::command('app:delete-expired-o-t-p')->dailyAt('23:59');
