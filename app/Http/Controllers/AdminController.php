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
        $today = Carbon::today();

        $todayTransactions = Transaction::with('intern:id,intern_name')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $todayCount = $todayTransactions->count();
        $totalCount = Transaction::count();
        $todayUniqueInterns = $todayTransactions->pluck('intern_id')->unique()->count();

        return Inertia::render('Admin/Dashboard', [
            'summary' => [
                'today_count' => $todayCount,
                'total_count' => $totalCount,
                'today_unique_interns' => $todayUniqueInterns,
                'date' => $today->toDateString(),
            ],
            'today_transactions' => $todayTransactions,
        ]);
    }
}
