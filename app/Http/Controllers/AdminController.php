<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(): Response
    {
        $todayStart = Carbon::today();
        $todayEnd = (clone $todayStart)->addDay();
        $monthStart = (clone $todayStart)->startOfMonth();
        $monthEnd = (clone $monthStart)->addMonth();

        $todayTransactions = Transaction::with('intern:id,intern_name')
            ->where('created_at', '>=', $todayStart)
            ->where('created_at', '<', $todayEnd)
            ->orderByDesc('created_at')
            ->get();

        $todayCount = $todayTransactions->count();
        $monthCount = Transaction::query()
            ->where('created_at', '>=', $monthStart)
            ->where('created_at', '<', $monthEnd)
            ->count();
        $todayUniqueInterns = $todayTransactions->pluck('intern_id')->unique()->count();

        return Inertia::render('Admin/Dashboard', [
            'summary' => [
                'today_count' => $todayCount,
                'month_count' => $monthCount,
                'month_label' => $monthStart->format('F Y'),
                'today_unique_interns' => $todayUniqueInterns,
                'date' => $todayStart->toDateString(),
            ],
            'today_transactions' => $todayTransactions,
        ]);
    }
}
