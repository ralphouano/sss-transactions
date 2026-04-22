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

        $todayTransactions = Transaction::with('intern:id,intern_name')
            ->where('created_at', '>=', $todayStart)
            ->where('created_at', '<', $todayEnd)
            ->orderByDesc('created_at')
            ->get();

        $todayCount = $todayTransactions->count();
        $totalCount = Transaction::count();
        $todayUniqueInterns = $todayTransactions->pluck('intern_id')->unique()->count();

        return Inertia::render('Admin/Dashboard', [
            'summary' => [
                'today_count' => $todayCount,
                'total_count' => $totalCount,
                'today_unique_interns' => $todayUniqueInterns,
                'date' => $todayStart->toDateString(),
            ],
            'today_transactions' => $todayTransactions,
        ]);
    }
}
