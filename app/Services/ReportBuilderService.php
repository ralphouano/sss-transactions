<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionType;
use App\Support\TransactionTypeFormatter;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ReportBuilderService
{
    public function buildSpreadsheet(string $month): Spreadsheet
    {
        $transactions = $this->getMonthlyTransactions($month);
        $templatePath = storage_path('app/templates/reports/SSS-e-center.xlsx');

        abort_unless(file_exists($templatePath), 500, 'Report template not found.');

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $startRow = 10;
        $baseEndRow = 14;
        $totalBaseRow = 16;
        $summaryBaseTemplateRow = 19;
        $baseCapacity = ($baseEndRow - $startRow) + 1;
        $extraRows = max(0, $transactions->count() - $baseCapacity);

        if ($extraRows > 0) {
            $sheet->insertNewRowBefore($baseEndRow + 1, $extraRows);
        }

        $row = $startRow;
        $counter = 1;

        foreach ($transactions as $transaction) {
            $sheet->setCellValue("A{$row}", $counter);
            $sheet->setCellValue("B{$row}", $transaction->created_at->translatedFormat('F j'));
            $sheet->setCellValue("C{$row}", $transaction->member_name);
            $formattedTypes = collect($transaction->transactions)
                ->map(fn (string $type) => TransactionTypeFormatter::format($type))
                ->implode(', ');
            $sheet->setCellValue("D{$row}", $formattedTypes);
            $sheet->setCellValue("E{$row}", $transaction->intern->intern_name ?? 'N/A');
            $counter++;
            $row++;
        }

        if ($row > $startRow) {
            $sheet->getStyle("A{$startRow}:E".($row - 1))
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("A{$startRow}:E".($row - 1))
                ->getFont()
                ->setSize(8)
                ->getColor()
                ->setARGB(Color::COLOR_BLACK);
        }

        // Total transaction count row is at A16:D16 in the base template.
        // When data rows expand beyond row 14, this row shifts down by extraRows.
        $totalRow = $totalBaseRow + $extraRows;
        $sheet->setCellValue("A{$totalRow}", 'TOTAL TRANSACTIONS');
        $sheet->setCellValue("D{$totalRow}", $transactions->count());
        $sheet->getStyle("A{$totalRow}:D{$totalRow}")
            ->getFont()
            ->setSize(8)
            ->getColor()
            ->setARGB(Color::COLOR_BLACK);

        $typeCounts = $this->buildTransactionTypeCounts($transactions);
        $transactionTypeRows = $this->getOrderedTransactionTypeRows($typeCounts);

        // Summary block starts at row 19 in the template.
        // If data rows were inserted before row 15, shift this block accordingly.
        $summaryBaseRow = $summaryBaseTemplateRow + $extraRows;
        $defaultSummaryRowsPerColumn = 8;
        $itemsPerColumn = max($defaultSummaryRowsPerColumn, (int) ceil(max(count($transactionTypeRows), 1) / 2));

        // Expand summary area when transaction types exceed template defaults.
        $additionalSummaryRows = max(0, $itemsPerColumn - $defaultSummaryRowsPerColumn);
        if ($additionalSummaryRows > 0) {
            $sheet->insertNewRowBefore($summaryBaseRow + $defaultSummaryRowsPerColumn, $additionalSummaryRows);
        }

        // Clean/overwrite summary cells first to keep output tidy.
        for ($i = 0; $i < $itemsPerColumn; $i++) {
            $sheet->setCellValue('B' . ($summaryBaseRow + $i), '');
            $sheet->setCellValue('C' . ($summaryBaseRow + $i), '');
            $sheet->setCellValue('D' . ($summaryBaseRow + $i), '');
            $sheet->setCellValue('E' . ($summaryBaseRow + $i), '');
        }

        foreach ($transactionTypeRows as $index => $item) {
            $columnGroup = $index < $itemsPerColumn ? 'left' : 'right';
            $position = $columnGroup === 'left' ? $index : $index - $itemsPerColumn;
            $targetRow = $summaryBaseRow + $position;

            $labelColumn = $columnGroup === 'left' ? 'B' : 'D';
            $countColumn = $columnGroup === 'left' ? 'C' : 'E';

            $sheet->setCellValue($labelColumn . $targetRow, strtoupper($item['label']));
            $sheet->setCellValue($countColumn . $targetRow, $item['count']);

            $sheet->getStyle($labelColumn . $targetRow . ':' . $countColumn . $targetRow)
                ->getFont()
                ->setSize(8)
                ->getColor()
                ->setARGB(Color::COLOR_BLACK);
        }

        return $spreadsheet;
    }

    private function getMonthlyTransactions(string $month): Collection
    {
        [$year, $monthNumber] = explode('-', $month);
        $start = Carbon::create((int) $year, (int) $monthNumber, 1)->startOfDay();
        $end = (clone $start)->addMonth();

        return Transaction::with('intern')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @param Collection<int, Transaction> $transactions
     * @return array<string, int>
     */
    private function buildTransactionTypeCounts(Collection $transactions): array
    {
        $counts = [];

        foreach ($transactions as $transaction) {
            foreach (($transaction->transactions ?? []) as $type) {
                $counts[$type] = ($counts[$type] ?? 0) + 1;
            }
        }

        return $counts;
    }

    /**
     * @param array<string, int> $typeCounts
     * @return array<int, array{key: string, label: string, count: int}>
     */
    private function getOrderedTransactionTypeRows(array $typeCounts): array
    {
        $rows = [];

        if (Schema::hasTable('transaction_types')) {
            $types = TransactionType::query()
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get(['key', 'label']);

            foreach ($types as $type) {
                $rows[] = [
                    'key' => $type->key,
                    'label' => $type->label,
                    'count' => $typeCounts[$type->key] ?? 0,
                ];
            }
        } else {
            // Fallback if table is unavailable.
            foreach ($typeCounts as $key => $count) {
                $rows[] = [
                    'key' => $key,
                    'label' => TransactionTypeFormatter::format($key),
                    'count' => $count,
                ];
            }
        }

        return $rows;
    }
}

