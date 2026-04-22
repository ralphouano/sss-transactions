<?php

namespace App\Jobs;

use App\Models\ReportExport;
use App\Services\ReportBuilderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class GenerateMonthlyReportExport implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $exportId) {}

    public function handle(ReportBuilderService $reportBuilder): void
    {
        $export = ReportExport::query()->find($this->exportId);
        if (! $export) {
            return;
        }

        $export->update([
            'status' => 'processing',
            'error_message' => null,
        ]);

        try {
            $spreadsheet = $reportBuilder->buildSpreadsheet($export->month);
            $filename = "transactions-{$export->month}-{$export->id}.xlsx";
            $outputPath = storage_path("app/private/exports/{$filename}");

            if (! is_dir(dirname($outputPath))) {
                mkdir(dirname($outputPath), 0755, true);
            }

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($outputPath);

            $export->update([
                'status' => 'completed',
                'filename' => $filename,
                'disk_path' => $outputPath,
                'finished_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to generate monthly report export.', [
                'report_export_id' => $this->exportId,
                'month' => $export->month,
                'error' => $exception->getMessage(),
            ]);

            $export->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);
        }
    }
}

