<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Channels\IndexChannelsRequest;
use App\Http\Requests\Channels\StoreChannelRequest;
use App\Http\Requests\Channels\UpdateChannelRequest;
use App\Models\Channel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ChannelsController extends Controller
{
    public function index(IndexChannelsRequest $request): View|JsonResponse
    {
        $query = $this->buildQuery($request);

        $perPage = max(5, min(100, (int) $request->integer('per_page', 10)));
        $records = (clone $query)->paginate($perPage)->withQueryString();

        if ($request->boolean('ajax')) {
            return response()->json([
                'html' => view('admin.channels.partials.table-rows', ['records' => $records->items()])->render(),
                'total_records' => $records->total(),
                'current_page' => $records->currentPage(),
                'per_page' => $records->perPage(),
            ]);
        }

        return view('admin.channels.index', [
            'records' => $records,
            'totalRecords' => $records->total(),
            'types' => Channel::types(),
            'purposes' => Channel::purposes(),
        ]);
    }

    public function store(StoreChannelRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $payload['is_active'] = $request->boolean('is_active', true);
        $payload['is_exclusive'] = $request->boolean('is_exclusive', false);

        Channel::query()->create($payload);

        return redirect()
            ->route('admin.channels.index')
            ->with('status', __('messages.admin.channels.messages.created'));
    }

    public function update(UpdateChannelRequest $request, Channel $channel): RedirectResponse
    {
        $payload = $request->validated();
        $payload['is_active'] = $request->boolean('is_active', true);
        $payload['is_exclusive'] = $request->boolean('is_exclusive', false);

        $channel->update($payload);

        return redirect()
            ->route('admin.channels.index')
            ->with('status', __('messages.admin.channels.messages.updated'));
    }

    public function destroy(Channel $channel): RedirectResponse
    {
        $channel->delete();

        return redirect()
            ->route('admin.channels.index')
            ->with('status', __('messages.admin.channels.messages.deleted'));
    }

    protected function buildQuery(IndexChannelsRequest $request): Builder
    {
        $sortBy = $this->resolveSortBy((string) $request->input('sort_by', 'name'));
        $sortOrder = strtolower((string) $request->input('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $search = trim((string) $request->input('search', ''));

        $query = Channel::query()->select('channels.*');

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('channels.name', 'like', "%{$search}%")
                    ->orWhere('channels.type', 'like', "%{$search}%")
                    ->orWhere('channels.purpose', 'like', "%{$search}%")
                    ->orWhere('channels.chat_id', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderBy($sortBy, $sortOrder)
            ->orderByDesc('channels.id');
    }

    protected function resolveSortBy(string $requestedSortBy): string
    {
        $allowed = [
            'channels.id',
            'channels.name',
            'channels.type',
            'channels.purpose',
            'channels.is_active',
            'channels.created_at',
        ];

        $normalized = str_contains($requestedSortBy, '.') ? $requestedSortBy : 'channels.'.$requestedSortBy;

        return in_array($normalized, $allowed, true) ? $normalized : 'channels.name';
    }
}
