<?php

namespace App\Http\Middleware;

use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $shouldLoadAdminTypeCounts = $request->routeIs('dashboard', 'admin.*');

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'adminTransactionTypeCounts' => fn () => $shouldLoadAdminTypeCounts
                ? $this->getTransactionTypeCounts()
                : [],
        ];
    }

    /**
     * @return array<int, array{key: string, count: int}>
     */
    private function getTransactionTypeCounts(): array
    {
        return Cache::remember('admin_transaction_type_counts', now()->addMinutes(5), function (): array {
            $counts = [];

            Transaction::query()
                ->select('transactions')
                ->cursor()
                ->each(function (Transaction $transaction) use (&$counts): void {
                    foreach (($transaction->transactions ?? []) as $type) {
                        $counts[$type] = ($counts[$type] ?? 0) + 1;
                    }
                });

            ksort($counts);

            return collect($counts)
                ->map(fn (int $count, string $key) => [
                    'key' => $key,
                    'count' => $count,
                ])
                ->values()
                ->all();
        });
    }
}
