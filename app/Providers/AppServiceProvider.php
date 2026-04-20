<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');

            $appUrl = (string) config('app.url');
            if (str_starts_with($appUrl, 'https://')) {
                URL::forceRootUrl(rtrim($appUrl, '/'));
            }
        }

        RateLimiter::for('intern-record', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('password-recovery', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        Vite::prefetch(concurrency: 3);
    }
}
