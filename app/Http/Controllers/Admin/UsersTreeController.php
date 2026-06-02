<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AffiliateTreeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsersTreeController extends Controller
{
    public function __construct(private readonly AffiliateTreeService $affiliateTreeService)
    {
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'root_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'depth' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $depth = (int) ($validated['depth'] ?? 6);
        $rootUserId = isset($validated['root_user_id']) ? (int) $validated['root_user_id'] : null;

        $rootUser = $rootUserId !== null
            ? User::query()->find($rootUserId)
            : null;

        $tree = $this->affiliateTreeService->buildTree($rootUser, $depth);
        $graph = $this->graphFromTree($tree);

        return view('admin.users-tree.index', [
            'tree' => $tree,
            'graph' => $graph,
            'rootUserId' => $rootUserId,
            'depth' => $depth,
            'rootOptions' => User::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function insights(User $user): JsonResponse
    {
        return response()->json([
            'data' => $this->affiliateTreeService->userInsights($user),
        ]);
    }

    /**
     * @param array<string, mixed> $tree
     * @return array{nodes: array<int, array<string, mixed>>, edges: array<int, array<string, int>>}
     */
    private function graphFromTree(array $tree): array
    {
        $nodes = [];
        $edges = [];

        $walk = function (array $node) use (&$walk, &$nodes, &$edges): void {
            $nodes[] = [
                'id' => (int) ($node['id'] ?? 0),
                'label' => (string) ($node['name'] ?? ''),
                'email' => (string) ($node['email'] ?? ''),
                'membership' => $node['membership'] ?? null,
                'commission_balance' => (float) ($node['commission_balance'] ?? 0),
                'joined_at' => $node['joined_at'] ?? null,
            ];

            $children = collect($node['children'] ?? []);

            $children->each(function (array $child) use (&$walk, &$edges, $node): void {
                $edges[] = [
                    'from' => (int) ($node['id'] ?? 0),
                    'to' => (int) ($child['id'] ?? 0),
                ];

                $walk($child);
            });
        };

        $walk($tree);

        return [
            'nodes' => collect($nodes)->unique('id')->values()->all(),
            'edges' => collect($edges)->values()->all(),
        ];
    }
}
