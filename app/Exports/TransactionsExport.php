<?php

namespace App\Exports;

use App\Models\Transaction;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private string $month)
    {
    }

    public function collection()
    {
        [$year, $monthNumber] = explode('-', $this->month);

        $query = Transaction::with('intern')
            ->whereYear('created_at', (int) $year)
            ->whereMonth('created_at', (int) $monthNumber);

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Member Name',
            'Transaction',
            'Intern Name',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->created_at->format('Y-m-d H:i:s'),
            $transaction->member_name,
            implode(', ', $transaction->transactions),
            $transaction->intern->intern_name ?? 'N/A',
        ];
    }
}
