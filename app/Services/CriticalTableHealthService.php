<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use RuntimeException;

class CriticalTableHealthService
{
    /**
     * @var array<int, string>
     */
    private array $requiredTables = [
        'transactions',
        'transaction_types',
        'system_settings',
    ];

    public function assertReady(): void
    {
        $missing = collect($this->requiredTables)
            ->filter(fn (string $table) => ! Schema::hasTable($table))
            ->values()
            ->all();

        if ($missing !== []) {
            throw new RuntimeException('Critical database tables missing: '.implode(', ', $missing));
        }
    }
}

