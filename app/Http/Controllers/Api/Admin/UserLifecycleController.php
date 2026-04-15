<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\InactiveUserPruneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserLifecycleController extends Controller
{
    public function __construct(private readonly InactiveUserPruneService $inactiveUserPruneService)
    {
    }

    public function pruneInactive(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'months' => ['nullable', 'integer', 'min:1', 'max:24'],
            'dry_run' => ['nullable', 'boolean'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $result = $this->inactiveUserPruneService->prune(
            (int) ($validated['months'] ?? 3),
            (bool) ($validated['dry_run'] ?? false),
            isset($validated['limit']) ? (int) $validated['limit'] : null
        );

        return response()->json([
            'message' => '🧹 Usuarios inactivos procesados correctamente. | 🧹 Inactive users processed successfully.',
            'message_es' => '🧹 Usuarios inactivos procesados correctamente.',
            'message_en' => '🧹 Inactive users processed successfully.',
            'meta' => [
                'months' => (int) ($validated['months'] ?? 3),
                'dry_run' => (bool) ($validated['dry_run'] ?? false),
                'limit' => isset($validated['limit']) ? (int) $validated['limit'] : null,
            ],
            'data' => $result,
        ]);
    }
}
