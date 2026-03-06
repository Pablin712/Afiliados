<?php

namespace App\Http\Controllers;

use App\Models\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\View\View;

class ActionController extends Controller
{
    public function index(Request $request): View|JsonResponse|Response|StreamedResponse
    {
        $query = $this->buildQuery($request);

        if ($request->filled('export')) {
            return $this->export($request, $query);
        }

        $perPage = max(5, min(100, (int) $request->integer('per_page', 10)));
        $records = (clone $query)->paginate($perPage);

        if ($request->boolean('ajax')) {
            return response()->json([
                'html' => view('actions.partials.table-rows', ['records' => $records->items()])->render(),
                'total_records' => $records->total(),
                'current_page' => $records->currentPage(),
                'per_page' => $records->perPage(),
            ]);
        }

        $canReport = $request->user()?->can('report actions') ?? false;

        return view('actions.index', [
            'records' => $records,
            'totalRecords' => $records->total(),
            'canReport' => $canReport,
        ]);
    }

    protected function buildQuery(Request $request): Builder
    {
        $sortBy = $this->resolveSortBy((string) $request->input('sort_by', 'created_at'));
        $sortOrder = strtolower((string) $request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $search = trim((string) $request->input('search', ''));

        $query = Action::query()
            ->select('actions.*')
            ->with('user:id,name,email')
            ->leftJoin('users', 'users.id', '=', 'actions.user_id')->orderBy('created_at', 'desc');

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('actions.module', 'like', "%{$search}%")
                    ->orWhere('actions.action', 'like', "%{$search}%")
                    ->orWhere('actions.method', 'like', "%{$search}%")
                    ->orWhere('actions.route', 'like', "%{$search}%")
                    ->orWhere('actions.url', 'like', "%{$search}%")
                    ->orWhere('actions.ip_address', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderBy($sortBy, $sortOrder)
            ->orderBy('actions.id', 'desc');
    }

    protected function resolveSortBy(string $requestedSortBy): string
    {
        $allowed = [
            'actions.id',
            'actions.module',
            'actions.action',
            'actions.method',
            'actions.route',
            'actions.url',
            'actions.ip_address',
            'actions.created_at',
            'users.name',
        ];

        $normalized = str_contains($requestedSortBy, '.') ? $requestedSortBy : 'actions.'.$requestedSortBy;

        return in_array($normalized, $allowed, true) ? $normalized : 'actions.created_at';
    }

    protected function export(Request $request, Builder $query): Response|StreamedResponse
    {
        if (! ($request->user()?->can('report actions') ?? false)) {
            throw new HttpException(403, 'No tienes permiso para generar reportes de auditoria.');
        }

        $format = strtolower((string) $request->input('export'));
        $timestamp = Carbon::now()->format('Ymd_His');
        $rows = (clone $query)->limit(20000)->get();

        return match ($format) {
            'csv' => $this->exportCsv($rows, $timestamp),
            'json' => $this->exportJson($rows, $timestamp),
            'excel' => $this->exportExcel($rows, $timestamp),
            'pdf' => $this->exportPdf($rows, $timestamp),
            default => throw new HttpException(422, 'Formato de exportacion no soportado.'),
        };
    }

    protected function exportCsv($rows, string $timestamp): StreamedResponse
    {
        $filename = "audit_actions_{$timestamp}.csv";

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['ID', 'Usuario', 'Email', 'Modulo', 'Accion', 'Metodo', 'Ruta', 'URL', 'IP', 'Fecha']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->user?->name,
                    $row->user?->email,
                    $row->module,
                    $row->action,
                    $row->method,
                    $row->route,
                    $row->url,
                    $row->ip_address,
                    optional($row->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function exportJson($rows, string $timestamp): Response
    {
        $filename = "audit_actions_{$timestamp}.json";

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'total_records' => $rows->count(),
            'data' => $rows->map(fn (Action $action) => [
                'id' => $action->id,
                'user' => $action->user?->name,
                'email' => $action->user?->email,
                'module' => $action->module,
                'action' => $action->action,
                'method' => $action->method,
                'route' => $action->route,
                'url' => $action->url,
                'ip_address' => $action->ip_address,
                'created_at' => optional($action->created_at)->toDateTimeString(),
            ])->values(),
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

    protected function exportExcel($rows, string $timestamp): StreamedResponse
    {
        $filename = "audit_actions_{$timestamp}.xls";

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fwrite($handle, implode("\t", ['ID', 'Usuario', 'Email', 'Modulo', 'Accion', 'Metodo', 'Ruta', 'URL', 'IP', 'Fecha'])."\n");

            foreach ($rows as $row) {
                $line = [
                    $row->id,
                    $row->user?->name,
                    $row->user?->email,
                    $row->module,
                    $row->action,
                    $row->method,
                    $row->route,
                    $row->url,
                    $row->ip_address,
                    optional($row->created_at)->format('Y-m-d H:i:s'),
                ];

                fwrite($handle, implode("\t", array_map(static fn ($value) => str_replace(["\t", "\r", "\n"], ' ', (string) $value), $line))."\n");
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    protected function exportPdf($rows, string $timestamp): Response
    {
        $pdf = Pdf::loadView('actions.exports.pdf', [
            'records' => $rows,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("audit_actions_{$timestamp}.pdf");
    }
}
