<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateMonthlyReportExport;
use App\Models\ReportExport;
use App\Models\Transaction;
use App\Services\ReportBuilderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use PhpOffice\PhpSpreadsheet\Writer\Html as HtmlWriter;
use Throwable;

class ReportController extends Controller
{
    public function __construct(private readonly ReportBuilderService $reportBuilder) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        $month = $validated['month'] ?? Carbon::now()->format('Y-m');
        $query = Transaction::with('intern:id,intern_name');

        [$year, $monthNumber] = explode('-', $month);
        $start = Carbon::create((int) $year, (int) $monthNumber, 1)->startOfDay();
        $end = (clone $start)->addMonth();
        $query->where('created_at', '>=', $start)
            ->where('created_at', '<', $end);
        $monthTotalCount = (clone $query)->count();
        $transactions = $query->orderByDesc('created_at')->paginate(50)->withQueryString();
        
        return Inertia::render('Admin/Reports', [
            'transactions' => $transactions,
            'month' => $month,
            'monthTotalCount' => $monthTotalCount,
        ]);
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        $month = $validated['month'] ?? Carbon::now()->format('Y-m');
        try {
            $spreadsheet = $this->reportBuilder->buildSpreadsheet($month);
            $filename = "transactions-{$month}.xlsx";
            $outputPath = storage_path("app/private/{$filename}");

            if (! is_dir(dirname($outputPath))) {
                mkdir(dirname($outputPath), 0755, true);
            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($outputPath);

            return response()->download($outputPath, $filename)->deleteFileAfterSend(true);
        } catch (Throwable $e) {
            Log::error('Report export failed.', [
                'month' => $month,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.reports.index', ['month' => $month])
                ->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    public function exportStatus(ReportExport $reportExport)
    {
        return response()->json([
            'id' => $reportExport->id,
            'status' => $reportExport->status,
            'errorMessage' => $reportExport->error_message,
            'downloadUrl' => $reportExport->status === 'completed'
                ? route('admin.reports.export.download', $reportExport)
                : null,
        ]);
    }

    public function exportDownload(ReportExport $reportExport): BinaryFileResponse
    {
        abort_unless($reportExport->status === 'completed', 409, 'Export is not ready yet.');
        abort_unless($reportExport->disk_path && file_exists($reportExport->disk_path), 404, 'Export file not found.');

        return response()
            ->download($reportExport->disk_path, $reportExport->filename ?? "transactions-{$reportExport->month}.xlsx")
            ->deleteFileAfterSend(true);
    }

    public function print(Request $request)
    {
        $validated = $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        $month = $validated['month'] ?? Carbon::now()->format('Y-m');
        try {
            $spreadsheet = $this->reportBuilder->buildSpreadsheet($month);
            $writer = new HtmlWriter($spreadsheet);
            $writer->setSheetIndex(0);

            ob_start();
            $writer->save('php://output');
            $html = (string) ob_get_clean();
        } catch (Throwable $e) {
            Log::error('Report print render failed.', [
                'month' => $month,
                'error' => $e->getMessage(),
            ]);

            return response(
                "<!doctype html><html><head><meta charset=\"utf-8\"><title>Print Error</title></head><body style=\"font-family:Arial,sans-serif;padding:24px;\"><h3>Unable to generate print preview</h3><p>{$e->getMessage()}</p></body></html>",
                422
            );
        }

        $styles = <<<CSS
@page { margin: 12mm; }
* { box-sizing: border-box; }
body {
    margin: 0;
    background: #eef3fb;
    color: #0f172a;
    font-family: "Segoe UI", Arial, sans-serif;
    display: flex;
    justify-content: center;
    padding: 16px;
}
.print-wrap {
    width: 100%;
    max-width: 1100px;
    background: #ffffff;
    border: 1px solid #dbe7ff;
    border-radius: 12px;
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
    padding: 18px;
}
.print-wrap table {
    margin: 0 auto !important;
}
.print-wrap img {
    border-radius: 4px;
}
@media print {
    body {
        background: #fff;
        display: block;
        padding: 0;
    }
    .print-wrap {
        max-width: none;
        border: none;
        border-radius: 0;
        box-shadow: none;
        padding: 0;
    }
}
CSS;

        return response(
            "<!doctype html><html><head><meta charset=\"utf-8\"><title>Print Report {$month}</title><style>{$styles}</style></head><body><div class=\"print-wrap\">{$html}</div><script>window.onload=function(){window.print();};</script></body></html>"
        );
    }

}
