<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        $month = $validated['month'] ?? null;
        $query = Transaction::with('intern:id,intern_name');

        if ($month) {
            [$year, $monthNumber] = explode('-', $month);
            $query->whereYear('created_at', (int) $year)
                ->whereMonth('created_at', (int) $monthNumber);
        }
        
        $transactions = $query->latest()->get();
        
        return Inertia::render('Admin/Reports', [
            'transactions' => $transactions,
            'month' => $month,
        ]);
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        $month = $validated['month'] ?? Carbon::now()->format('Y-m');

        return Excel::download(
            new TransactionsExport($month),
            'transactions-' . $month . '.csv'
        );
    }
}
