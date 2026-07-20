<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\UserAgentParser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UsersAdminController extends Controller
{
    public function index(Request $request): View|JsonResponse|StreamedResponse
    {
        $perPage   = max(5, min(100, (int) $request->integer('per_page', 15)));
        $search    = trim((string) $request->input('search', ''));
        $sortOrder = strtolower((string) $request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortBy    = $this->resolveSortBy((string) $request->input('sort_by', 'id'));
        $export    = $request->input('export');

        $query = User::query()
            ->select('users.*')
            ->with(['sponsor', 'membership.membershipType', 'roles'])
            ->orderBy($sortBy, $sortOrder);

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('users.affiliate_code', 'like', "%{$search}%")
                  ->orWhere('users.phone', 'like', "%{$search}%");
            });
        }

        if ($export === 'excel') {
            $users = (clone $query)->with('userBanks')->get();
            return $this->exportExcel($users);
        }

        $records = $query->paginate($perPage);

        // Active sessions per user (active = last activity within 15 min, not kicked)
        $activeSessionsThreshold = now()->subMinutes(15)->timestamp;
        $activeSessions = DB::table('sessions')
            ->whereNotNull('user_id')
            ->whereNull('kicked_at')
            ->where('last_activity', '>=', $activeSessionsThreshold)
            ->select('user_id', 'ip_address', 'user_agent', 'last_activity')
            ->get()
            ->keyBy('user_id')
            ->map(function (object $s): array {
                $parsed = UserAgentParser::parse((string) ($s->user_agent ?? ''));

                return [
                    'browser'       => $parsed['browser'],
                    'os'            => $parsed['os'],
                    'ip'            => $s->ip_address ?? '—',
                    'last_activity' => $s->last_activity,
                ];
            });

        if ($request->boolean('ajax')) {
            return response()->json([
                'html'          => view('admin.users.partials.table-rows', [
                    'records'        => $records->items(),
                    'activeSessions' => $activeSessions,
                ])->render(),
                'total_records' => $records->total(),
                'current_page'  => $records->currentPage(),
                'per_page'      => $records->perPage(),
            ]);
        }

        $sponsors = User::query()
            ->select('id', 'name', 'email', 'affiliate_code')
            ->orderBy('name')
            ->get()
            ->map(function (User $user): array {
                $affiliateCode = $user->affiliate_code ?? ('#'.$user->id);

                return [
                    'id' => $user->id,
                    'name' => $user->name.' ('.$affiliateCode.') - '.$user->email,
                ];
            });

        return view('admin.users.index', [
            'records'        => $records,
            'sponsors'       => $sponsors,
            'totalRecords'   => $records->total(),
            'activeSessions' => $activeSessions,
        ]);
    }

    public function searchSponsors(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('q', ''));
        $exclude = (int) $request->integer('exclude', 0);

        $query = User::query()
            ->select('id', 'name', 'email', 'affiliate_code')
            ->orderBy('name');

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('affiliate_code', 'like', "%{$search}%");
            });
        }

        if ($exclude > 0) {
            $query->where('id', '!=', $exclude);
        }

        $users = $query->limit(30)->get();

        return response()->json([
            'results' => $users->map(function (User $u): array {
                $affiliateCode = $u->affiliate_code ?? ('#'.$u->id);

                return [
                    'id'   => $u->id,
                    'text' => $u->name.' ('.$affiliateCode.') — '.$u->email,
                ];
            }),
        ]);
    }

    public function updateRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:make_teacher,remove_teacher'],
        ]);

        if ($user->hasRole('admin')) {
            return response()->json(['message' => 'No se puede modificar el rol de un administrador.'], 422);
        }

        if ($validated['action'] === 'make_teacher') {
            $user->assignRole('teacher');
        } else {
            $user->removeRole('teacher');
        }

        return response()->json([
            'success'    => true,
            'is_teacher' => $user->hasRole('teacher'),
        ]);
    }

    public function updateSponsor(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'sponsor_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $newSponsorId = (int) $validated['sponsor_id'];

        if ($newSponsorId === $user->id) {
            return back()->with('error', __('messages.admin.users.error_self_sponsor'));
        }

        // Prevent circular references: the new sponsor must not be a descendant of this user.
        if ($this->isDescendant($user, $newSponsorId)) {
            return back()->with('error', __('messages.admin.users.error_circular_sponsor'));
        }

        $user->update(['sponsor_id' => $newSponsorId]);

        return back()->with('status', __('messages.admin.users.sponsor_updated'));
    }

    private function exportExcel(\Illuminate\Support\Collection $users): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Usuarios');

        $headers = [
            'ID', 'Nombre', 'Email', 'Teléfono', 'Código Afiliado',
            'Patrocinador', 'Membresía', 'Estado Membresía', 'Vencimiento Membresía', 'Registrado',
            'Banco - Titular', 'Banco - Nombre', 'Banco - Tipo', 'Banco - Número', 'Banco - Identificación',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $sheet->getStyle('A1:O1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F4FFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        $row = 2;
        foreach ($users as $user) {
            $bank = $user->userBanks->firstWhere('is_default', true) ?? $user->userBanks->first();

            $sheet->fromArray([
                $user->id,
                $user->name,
                $user->email,
                $user->phone ?? '',
                $user->affiliate_code ?? '',
                $user->sponsor?->name ?? '',
                $user->membership?->membershipType?->name ?? '',
                $user->membership?->status ?? '',
                $user->membership?->expires_at?->format('Y-m-d') ?? '',
                $user->created_at?->format('Y-m-d') ?? '',
                $bank?->owner ?? '',
                $bank?->bank_name ?? '',
                $bank?->type ?? '',
                $bank?->number ?? '',
                $bank?->identification ?? '',
            ], null, "A{$row}");

            $row++;
        }

        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'usuarios_' . now()->format('Y-m-d_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Check whether $candidateId is a descendant of $user in the affiliate tree.
     */
    protected function isDescendant(User $user, int $candidateId): bool
    {
        $frontier = [$user->id];
        $visited  = [];

        while (! empty($frontier)) {
            $currentId = array_shift($frontier);

            if (isset($visited[$currentId])) {
                continue;
            }
            $visited[$currentId] = true;

            $children = User::query()
                ->select('id')
                ->where('sponsor_id', $currentId)
                ->whereColumn('id', '!=', 'sponsor_id')
                ->pluck('id')
                ->all();

            foreach ($children as $childId) {
                if ((int) $childId === $candidateId) {
                    return true;
                }
                $frontier[] = (int) $childId;
            }
        }

        return false;
    }

    protected function resolveSortBy(string $requested): string
    {
        $allowed = ['id', 'name', 'email', 'phone', 'created_at'];

        return in_array($requested, $allowed, true) ? $requested : 'id';
    }
}
