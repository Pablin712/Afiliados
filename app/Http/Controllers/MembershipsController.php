<?php

namespace App\Http\Controllers;

use App\Http\Requests\Memberships\IndexMembershipsRequest;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Services\MembershipTierService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MembershipsController extends Controller
{
    public function __construct(private readonly MembershipTierService $membershipTierService)
    {
    }

    public function index(IndexMembershipsRequest $request): View|JsonResponse|Response|StreamedResponse
    {
        $query = $this->buildQuery($request);
        $membershipTypes = MembershipType::query()->select('id', 'name')->orderBy('name')->get();
        $statusOptions = ['active', 'free', 'expired', 'pending_payment'];
        $canEdit = $request->user()?->can('edit memberships') ?? false;

        if ($request->filled('export')) {
            return $this->export($request, $query);
        }

        $perPage = max(5, min(100, (int) $request->integer('per_page', 10)));
        $records = (clone $query)->paginate($perPage);
        $rankExplanations = $this->membershipTierService->explainAll();

        if ($request->boolean('ajax')) {
            return response()->json([
                'html' => view('memberships.partials.table-rows', [
                    'records' => $records->items(),
                    'membershipTypes' => $membershipTypes,
                    'statusOptions' => $statusOptions,
                    'canEdit' => $canEdit,
                    'rankExplanations' => $rankExplanations,
                ])->render(),
                'total_records' => $records->total(),
                'current_page' => $records->currentPage(),
                'per_page' => $records->perPage(),
            ]);
        }

        $canReport = $request->user()?->can('report memberships') ?? false;
        $filters = [
            'status' => (string) $request->input('status', ''),
            'membership_type_id' => (string) $request->input('membership_type_id', ''),
        ];

        return view('memberships.index', [
            'records' => $records,
            'totalRecords' => $records->total(),
            'canReport' => $canReport,
            'canEdit' => $canEdit,
            'membershipTypes' => $membershipTypes,
            'filters' => $filters,
            'statusOptions' => $statusOptions,
            'rankExplanations' => $rankExplanations,
        ]);
    }

    protected function buildQuery(Request $request): Builder
    {
        $sortBy = $this->resolveSortBy((string) $request->input('sort_by', 'created_at'));
        $sortOrder = strtolower((string) $request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', ''));
        $membershipTypeId = (int) $request->integer('membership_type_id');

        $query = Membership::query()
            ->select('memberships.*')
            ->addSelect([
                'users.name as user_name',
                'users.email as user_email',
                'membership_types.name as membership_type_name',
            ])
            ->leftJoin('users', 'users.id', '=', 'memberships.user_id')
            ->leftJoin('membership_types', 'membership_types.id', '=', 'memberships.membership_type_id');

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('memberships.id', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('membership_types.name', 'like', "%{$search}%")
                    ->orWhere('memberships.status', 'like', "%{$search}%");
            });
        }

        if ($status !== '') {
            $query->where('memberships.status', $status);
        }

        if ($membershipTypeId > 0) {
            $query->where('memberships.membership_type_id', $membershipTypeId);
        }

        return $query
            ->orderBy($sortBy, $sortOrder)
            ->orderBy('memberships.id', 'desc');
    }

    protected function resolveSortBy(string $requestedSortBy): string
    {
        $allowed = [
            'memberships.id',
            'users.name',
            'membership_types.name',
            'memberships.status',
            'memberships.started_at',
            'memberships.expires_at',
            'memberships.created_at',
        ];

        $normalized = match ($requestedSortBy) {
            'id', 'status', 'started_at', 'expires_at', 'created_at' => 'memberships.'.$requestedSortBy,
            'user_name' => 'users.name',
            'membership_type_name' => 'membership_types.name',
            default => str_contains($requestedSortBy, '.') ? $requestedSortBy : 'memberships.'.$requestedSortBy,
        };

        return in_array($normalized, $allowed, true) ? $normalized : 'memberships.created_at';
    }

    protected function export(Request $request, Builder $query): Response|StreamedResponse
    {
        if (! ($request->user()?->can('report memberships') ?? false)) {
            throw new HttpException(403, 'No tienes permiso para generar reportes de membresias.');
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
        $filename = "memberships_{$timestamp}.csv";

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['ID', 'Usuario', 'Email', 'Tipo de Membresia', 'Estado', 'Inicio', 'Vencimiento', 'Creado']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->user_name,
                    $row->user_email,
                    $row->membership_type_name,
                    $row->status,
                    optional($row->started_at)->format('Y-m-d H:i:s'),
                    optional($row->expires_at)->format('Y-m-d H:i:s'),
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
        $filename = "memberships_{$timestamp}.json";

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'total_records' => $rows->count(),
            'data' => $rows->map(fn (Membership $membership) => [
                'id' => $membership->id,
                'user' => $membership->user_name,
                'email' => $membership->user_email,
                'membership_type' => $membership->membership_type_name,
                'status' => $membership->status,
                'started_at' => optional($membership->started_at)->toDateTimeString(),
                'expires_at' => optional($membership->expires_at)->toDateTimeString(),
                'created_at' => optional($membership->created_at)->toDateTimeString(),
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
        $filename = "memberships_{$timestamp}.xls";

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fwrite($handle, implode("\t", ['ID', 'Usuario', 'Email', 'Tipo de Membresia', 'Estado', 'Inicio', 'Vencimiento', 'Creado'])."\n");

            foreach ($rows as $row) {
                $line = [
                    $row->id,
                    $row->user_name,
                    $row->user_email,
                    $row->membership_type_name,
                    $row->status,
                    optional($row->started_at)->format('Y-m-d H:i:s'),
                    optional($row->expires_at)->format('Y-m-d H:i:s'),
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
        $pdf = Pdf::loadView('memberships.exports.pdf', [
            'records' => $rows,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("memberships_{$timestamp}.pdf");
    }

    public function store(Request $request)
    {
        // TODO: Use StoreMembershipsRequest and implement create flow.
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        if (! ($request->user()?->can('edit memberships') ?? false)) {
            throw new HttpException(403, 'No tienes permiso para editar membresias.');
        }

        $membership = Membership::query()
            ->with(['membershipType'])
            ->findOrFail($id);

        $validated = $request->validate([
            'membership_type_id' => ['required', 'integer', Rule::exists('membership_types', 'id')],
            'status' => ['required', 'string', Rule::in(['active', 'free', 'expired', 'pending_payment'])],
            'started_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $membershipType = MembershipType::query()->findOrFail((int) $validated['membership_type_id']);
        $typeName = strtolower((string) $membershipType->name);

        if ($typeName === 'free') {
            $validated['status'] = 'free';
            $validated['started_at'] = null;
            $validated['expires_at'] = null;
        } elseif ($validated['status'] === 'free') {
            return back()->with('error', __('memberships.messages.invalid_paid_status'));
        }

        $startedAt = isset($validated['started_at']) && $validated['started_at'] !== null
            ? Carbon::parse((string) $validated['started_at'])
            : null;

        $expiresAt = isset($validated['expires_at']) && $validated['expires_at'] !== null
            ? Carbon::parse((string) $validated['expires_at'])
            : null;

        if ($startedAt && $expiresAt && $expiresAt->lt($startedAt)) {
            return back()->with('error', __('memberships.messages.invalid_dates'));
        }

        if ($typeName !== 'free' && in_array($validated['status'], ['active', 'expired', 'pending_payment'], true) && ! $startedAt) {
            $startedAt = now();
        }

        $membership->membership_type_id = (int) $membershipType->id;
        $membership->status = (string) $validated['status'];
        $membership->started_at = $startedAt;
        $membership->expires_at = $expiresAt;
        $membership->save();

        return back()->with('status', __('memberships.messages.updated'));
    }

    public function destroy(int $id)
    {
        // TODO: Implement delete flow with permission delete memberships.
    }
}
