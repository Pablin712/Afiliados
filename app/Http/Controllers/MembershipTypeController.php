<?php

namespace App\Http\Controllers;

use App\Http\Requests\MembershipTypes\IndexMembershipTypesRequest;
use App\Http\Requests\MembershipTypes\StoreMembershipTypeRequest;
use App\Http\Requests\MembershipTypes\UpdateMembershipTypeRequest;
use App\Models\MembershipType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MembershipTypeController extends Controller
{
    public function index(IndexMembershipTypesRequest $request): View|JsonResponse
    {
        $query = $this->buildQuery($request);

        $perPage = max(5, min(100, (int) $request->integer('per_page', 10)));
        $records = (clone $query)->paginate($perPage);

        if ($request->boolean('ajax')) {
            return response()->json([
                'html' => view('membership_types.partials.table-rows', ['records' => $records->items()])->render(),
                'total_records' => $records->total(),
                'current_page' => $records->currentPage(),
                'per_page' => $records->perPage(),
            ]);
        }

        return view('membership_types.index', [
            'records' => $records,
            'totalRecords' => $records->total(),
        ]);
    }

    public function store(StoreMembershipTypeRequest $request): RedirectResponse
    {
        MembershipType::query()->create($request->validated());

        return redirect()
            ->route('membership-types.index')
            ->with('status', __('membership_types.messages.created'));
    }

    public function update(UpdateMembershipTypeRequest $request, MembershipType $membershipType): RedirectResponse
    {
        $membershipType->update($request->validated());

        return redirect()
            ->route('membership-types.index')
            ->with('status', __('membership_types.messages.updated'));
    }

    public function destroy(MembershipType $membershipType): RedirectResponse
    {
        if ($membershipType->memberships()->exists()) {
            return redirect()
                ->route('membership-types.index')
                ->with('error', __('membership_types.messages.delete_blocked'));
        }

        $membershipType->delete();

        return redirect()
            ->route('membership-types.index')
            ->with('status', __('membership_types.messages.deleted'));
    }

    protected function buildQuery(IndexMembershipTypesRequest $request): Builder
    {
        $sortBy = $this->resolveSortBy((string) $request->input('sort_by', 'name'));
        $sortOrder = strtolower((string) $request->input('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $search = trim((string) $request->input('search', ''));

        $query = MembershipType::query()->select('membership_types.*');

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('membership_types.name', 'like', "%{$search}%")
                    ->orWhere('membership_types.affiliates_required', 'like', "%{$search}%")
                    ->orWhere('membership_types.cost', 'like', "%{$search}%")
                    ->orWhere('membership_types.profit', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderBy($sortBy, $sortOrder)
            ->orderBy('membership_types.id', 'desc');
    }

    protected function resolveSortBy(string $requestedSortBy): string
    {
        $allowed = [
            'membership_types.id',
            'membership_types.name',
            'membership_types.affiliates_required',
            'membership_types.cost',
            'membership_types.profit',
            'membership_types.created_at',
        ];

        $normalized = str_contains($requestedSortBy, '.') ? $requestedSortBy : 'membership_types.'.$requestedSortBy;

        return in_array($normalized, $allowed, true) ? $normalized : 'membership_types.name';
    }
}
