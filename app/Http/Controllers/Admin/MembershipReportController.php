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
use Symfony\Component\HttpKernel\Exception\HttpException;

class MembershipReportController extends Controller
{
    public function __construct(private readonly MembershipReportService $membershipReportService)
    {
    }

    public function index(Request $request): View|Response
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

    protected function export(Request $request, array $report, string $segment, ?array $segmentData): Response
    {
        if (! ($request->user()?->can('report memberships') ?? false)) {
            throw new HttpException(403, 'No tienes permiso para generar reportes de membresias.');
        }

        $format = strtolower((string) $request->input('export'));

        if ($format !== 'pdf') {
            throw new HttpException(422, 'Formato de exportacion no soportado.');
        }

        $timestamp = Carbon::now()->format('Ymd_His');

        if ($segment !== 'all' && $segmentData !== null) {
            $pdf = Pdf::loadView('admin.membership-report.exports.segment-pdf', [
                'segment' => $segment,
                'segmentData' => $segmentData,
                'generatedAt' => now(),
            ])->setPaper('a4', 'portrait');

            return $pdf->download("reporte_membresias_{$segment}_{$timestamp}.pdf");
        }

        $pdf = Pdf::loadView('admin.membership-report.exports.pdf', [
            'report' => $report,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download("reporte_membresias_{$timestamp}.pdf");
    }
}
