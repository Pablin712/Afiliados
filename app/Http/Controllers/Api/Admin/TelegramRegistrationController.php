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
     * Called by n8n's Telegram Trigger for every message it receives (private
     * chats, groups, and topics alike) — most of those are just chatter, not
     * a registration code, so this must silently no-op on anything that isn't
     * a genuine private-chat code submission (see the two early-return guards
     * below), rather than surface those as errors back to n8n.
     *
     * Responses:
     *   200 – ignored: group/supergroup chat, or text that isn't code-shaped
     *   201 – code valid, chat_id registered, confirmation message sent to user
     *   200 – code valid but chat_id already registered, info message sent
     *   400 – well-formed code that doesn't match any user
     */
    public function registerChatId(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chat_id' => ['required', 'integer'],
            // Nullable: non-text updates (photos, stickers, joins, etc.) arrive
            // with no message text at all — those must be ignored below, not
            // rejected here with a validation error.
            'code'    => ['nullable', 'string', 'max:4000'],
        ]);

        $telegramChatId = (int) $validated['chat_id'];

        // Groups and supergroups always have a negative chat_id; only private
        // chats (positive id) can be someone registering their own code.
        if ($telegramChatId < 0) {
            return response()->json(['message' => 'Ignored: group chat.', 'registered' => false]);
        }

        $code = strtoupper(trim((string) ($validated['code'] ?? '')));

        // telegram_code is always exactly 10 uppercase alphanumeric characters
        // (see User::boot()); anything else is just ordinary chat text.
        if (! preg_match('/^[A-Z0-9]{10}$/', $code)) {
            return response()->json(['message' => 'Ignored: not a code.', 'registered' => false]);
        }

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
