<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsersAdminController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $perPage   = max(5, min(100, (int) $request->integer('per_page', 15)));
        $search    = trim((string) $request->input('search', ''));
        $sortOrder = strtolower((string) $request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortBy    = $this->resolveSortBy((string) $request->input('sort_by', 'id'));

        $query = User::query()
            ->select('users.*')
            ->with(['sponsor', 'membership.membershipType'])
            ->orderBy($sortBy, $sortOrder);

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('users.affiliate_code', 'like', "%{$search}%");
            });
        }

        $records = $query->paginate($perPage);

        if ($request->boolean('ajax')) {
            return response()->json([
                'html'          => view('admin.users.partials.table-rows', ['records' => $records->items()])->render(),
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
            'records'      => $records,
            'sponsors'     => $sponsors,
            'totalRecords' => $records->total(),
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
        $allowed = ['id', 'name', 'email', 'created_at'];

        return in_array($requested, $allowed, true) ? $requested : 'id';
    }
}
