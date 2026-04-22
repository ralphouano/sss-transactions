<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\CriticalTableHealthService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:health-check', function (CriticalTableHealthService $healthService) {
    $healthService->assertReady();
    $this->info('Application health check passed.');
})->purpose('Validate critical application dependencies and tables');
