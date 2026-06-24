<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramRegistrationController extends Controller
{
    public function __construct(private readonly TelegramService $telegramService) {}

    
    /**
     * Register a Telegram chat_id for a user identified by their telegram_code.
     *
     * Called b
     * y n8n when a user sends their unique code to the Telegram bot.
     *
     * Responses:
     *   201 – code valid, chat_id registered, confirmation message sent to user
     *   200 – code valid but chat_id already registered, info message sent
     *   400 – invalid or unrecognised code, no message sent
     */
    public function registerChatId(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chat_id' => ['required', 'integer'],
            'code'    => ['required', 'string', 'max:20'],
        ]);

        $telegramChatId = (int) $validated['chat_id'];
        $code = strtoupper(trim((string) $validated['code']));

        $user = User::query()->where('telegram_code', $code)->first();

        if (! $user instanceof User) {
            return response()->json(['message' => 'Invalid code.'], 400);
        }

        if ($user->telegram_chat_id !== null) {
            $this->telegramService->sendMessage(
                $telegramChatId,
                'Tu Telegram ya ha sido registrado anteriormente en AET Trader Academy. Si necesitas actualizarlo, inicia sesión y ve a tu perfil.'
            );

            return response()->json([
                'message'    => 'Telegram chat_id already registered for this user.',
                'registered' => false,
            ]);
        }

        $user->telegram_chat_id = $telegramChatId;
        $user->save();

        $this->telegramService->sendMessage(
            $telegramChatId,
            "✅ ¡Tu Telegram ha sido registrado exitosamente en AET Trader Academy!\n\nBienvenido/a, {$user->name}. Ya tienes acceso completo a los grupos exclusivos."
        );

        return response()->json([
            'message'    => 'Telegram chat_id registered successfully.',
            'registered' => true,
            'user_id'    => (int) $user->id,
        ], 201);
    }
}
