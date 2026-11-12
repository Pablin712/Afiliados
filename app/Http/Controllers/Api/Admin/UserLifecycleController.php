<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class UserLifecycleController extends Controller
{
    /**
     * Users used to be deleted here after three months without logging in.
     * The owner deletes them by hand from the users admin instead, so this
     * no longer deletes anyone. It still answers successfully because an n8n
     * schedule calls it before cleaning the WhatsApp and Telegram groups, and
     * a failure here would stop that clean-up too.
     */
    public function pruneInactive(): JsonResponse
    {
        return response()->json([
            'message' => 'La eliminación automática de usuarios está desactivada; se eliminan desde el panel. | Automatic user deletion is disabled; users are deleted from the admin panel.',
            'message_es' => 'La eliminación automática de usuarios está desactivada; se eliminan desde el panel.',
            'message_en' => 'Automatic user deletion is disabled; users are deleted from the admin panel.',
            'data' => [
                'deleted' => 0,
            ],
        ]);
    }
}
