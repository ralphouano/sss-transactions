<?php

namespace App\Providers;

use App\Models\TransactionType;
use App\Models\User;
use App\Policies\TransactionTypePolicy;
use App\Policies\UserPolicy;
use App\Services\CriticalTableHealthService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(TransactionType::class, TransactionTypePolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        if ($this->app->environment('production') && ! $this->isMigrationCommand()) {
            app(CriticalTableHealthService::class)->assertReady();
        }

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

    private function isMigrationCommand(): bool
    {
        if (! $this->app->runningInConsole()) {
            return false;
        }

        $argv = $_SERVER['argv'] ?? [];
        $command = $argv[1] ?? '';

        return in_array($command, ['migrate', 'migrate:fresh', 'migrate:refresh', 'migrate:reset', 'migrate:rollback'], true);
    }
}
