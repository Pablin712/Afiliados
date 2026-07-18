<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MembershipReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MembershipReportController extends Controller
{
    public function __construct(private readonly MembershipReportService $membershipReportService)
    {
    }

    public function index(Request $request): View|Response|StreamedResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'segment' => ['nullable', Rule::in(MembershipReportService::SEGMENTS)],
        ]);

        $to = isset($validated['to'])
            ? Carbon::createFromFormat('Y-m-d', $validated['to'])->endOfDay()
            : now()->endOfDay();

        $from = isset($validated['from'])
            ? Carbon::createFromFormat('Y-m-d', $validated['from'])->startOfDay()
            : $to->copy()->startOfMonth();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $segment = (string) ($validated['segment'] ?? 'all');

        $report = $this->membershipReportService->build($from, $to);
        $segmentData = $segment !== 'all' ? $this->membershipReportService->segmentUsers($segment) : null;

        if ($request->filled('export')) {
            return $this->export($request, $report, $segment, $segmentData);
        }

        return view('admin.membership-report.index', [
            'report' => $report,
            'segment' => $segment,
            'segmentData' => $segmentData,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    protected function export(Request $request, array $report, string $segment, ?array $segmentData): Response|StreamedResponse
    {
        if (! ($request->user()?->can('report memberships') ?? false)) {
            throw new HttpException(403, 'No tienes permiso para generar reportes de membresias.');
        }

        $format = strtolower((string) $request->input('export'));
        $timestamp = Carbon::now()->format('Ymd_His');

        if ($segment !== 'all' && $segmentData !== null) {
            return match ($format) {
                'csv' => $this->exportSegmentCsv($segment, $segmentData, $timestamp),
                'json' => $this->exportSegmentJson($segment, $segmentData, $timestamp),
                'excel' => $this->exportSegmentExcel($segment, $segmentData, $timestamp),
                'pdf' => $this->exportSegmentPdf($segment, $segmentData, $timestamp),
                default => throw new HttpException(422, 'Formato de exportacion no soportado.'),
            };
        }

        if ($format !== 'pdf') {
            throw new HttpException(422, 'Formato de exportacion no soportado.');
        }

        $pdf = Pdf::loadView('admin.membership-report.exports.pdf', [
            'report' => $report,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download("reporte_membresias_{$timestamp}.pdf");
    }

    /**
     * Column headers and row values for the currently selected segment, shared by the
     * csv/excel/json exporters so every format reflects exactly what was filtered.
     *
     * @return array{0: list<string>, 1: list<list<string>>}
     */
    protected function segmentExportTable(string $segment, array $segmentData): array
    {
        if (in_array($segment, ['free', 'non_renewed'], true)) {
            $headers = [
                __('membership_report.columns.user'),
                __('membership_report.columns.email'),
                __('membership_report.columns.joined_at'),
            ];

            if ($segment === 'free') {
                $headers[] = __('membership_report.columns.previously_paid');
            }

            $headers[] = __('membership_report.columns.previous_type');
            $headers[] = __('membership_report.columns.downgraded_at');

            $rows = array_map(function (array $row) use ($segment): array {
                $line = [$row['user_name'], $row['user_email'], $row['joined_at'] ?? '-'];

                if ($segment === 'free') {
                    $line[] = $row['previously_paid'] ? __('membership_report.booleans.yes') : __('membership_report.booleans.no');
                }

                $line[] = $row['previous_type'] ?? '-';
                $line[] = $row['downgraded_at'] ?? '-';

                return $line;
            }, $segmentData['records']);

            return [$headers, $rows];
        }

        $headers = [
            __('membership_report.columns.user'),
            __('membership_report.columns.email'),
            __('membership_report.columns.membership_type'),
            __('membership_report.columns.joined_at'),
            __('membership_report.columns.started_at'),
            __('membership_report.columns.expires_at'),
        ];

        $rows = array_map(fn (array $row): array => [
            $row['user_name'],
            $row['user_email'],
            $row['membership_type_name'],
            $row['joined_at'] ?? '-',
            $row['started_at'] ?? '-',
            $row['expires_at'] ?? '-',
        ], $segmentData['records']);

        return [$headers, $rows];
    }

    protected function exportSegmentCsv(string $segment, array $segmentData, string $timestamp): StreamedResponse
    {
        [$headers, $rows] = $this->segmentExportTable($segment, $segmentData);
        $filename = "reporte_membresias_{$segment}_{$timestamp}.csv";

        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function exportSegmentJson(string $segment, array $segmentData, string $timestamp): Response
    {
        $filename = "reporte_membresias_{$segment}_{$timestamp}.json";

        $payload = [
            'segment' => $segment,
            'generated_at' => now()->toIso8601String(),
            'total_records' => $segmentData['total'],
            'data' => $segmentData['records'],
        ];

        return response()->make(
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            200,
            [
                'Content-Type' => 'application/json; charset=UTF-8',
                'Content-Disposition' => "attachment; filename={$filename}",
            ],
        );
    }

    protected function exportSegmentExcel(string $segment, array $segmentData, string $timestamp): StreamedResponse
    {
        [$headers, $rows] = $this->segmentExportTable($segment, $segmentData);
        $filename = "reporte_membresias_{$segment}_{$timestamp}.xls";

        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fwrite($handle, implode("\t", $headers)."\n");

            foreach ($rows as $row) {
                fwrite($handle, implode("\t", array_map(static fn ($value) => str_replace(["\t", "\r", "\n"], ' ', (string) $value), $row))."\n");
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    protected function exportSegmentPdf(string $segment, array $segmentData, string $timestamp): Response
    {
        $pdf = Pdf::loadView('admin.membership-report.exports.segment-pdf', [
            'segment' => $segment,
            'segmentData' => $segmentData,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download("reporte_membresias_{$segment}_{$timestamp}.pdf");
    }
}
