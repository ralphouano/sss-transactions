<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateMonthlyReportExport;
use App\Models\ReportExport;
use App\Models\Transaction;
use App\Services\ReportBuilderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use PhpOffice\PhpSpreadsheet\Writer\Html as HtmlWriter;

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
        $transactions = $query->orderBy('created_at')->paginate(50)->withQueryString();
        
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
        $reportExport = ReportExport::query()->create([
            'month' => $month,
            'status' => 'queued',
            'requested_by' => $request->user()?->id,
        ]);

        GenerateMonthlyReportExport::dispatch($reportExport->id);

        return response()->json([
            'exportId' => $reportExport->id,
            'status' => $reportExport->status,
        ]);
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
        $spreadsheet = $this->reportBuilder->buildSpreadsheet($month);
        $writer = new HtmlWriter($spreadsheet);
        $writer->setSheetIndex(0);

        ob_start();
        $writer->save('php://output');
        $html = (string) ob_get_clean();

        return response(
            "<!doctype html><html><head><meta charset=\"utf-8\"><title>Print Report {$month}</title><style>@page{margin:12mm;}body{margin:0;background:#fff;font-family:Arial,sans-serif;display:flex;justify-content:center;}.print-wrap{width:100%;display:flex;justify-content:center;padding:0;}table{margin:0 auto !important;}@media print{body{background:#fff;display:block;}.print-wrap{display:block;padding:0;}table{margin:0 auto !important;}}</style></head><body><div class=\"print-wrap\">{$html}</div><script>window.onload=function(){window.print();};</script></body></html>"
        );
    }

}
