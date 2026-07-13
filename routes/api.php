<?php

use App\Http\Controllers\Api\Admin\AffiliateTreeController;
use App\Http\Controllers\Api\Admin\FinancialStatsController;
use App\Http\Controllers\Api\Admin\GroupController;
use App\Http\Controllers\Api\Admin\MembershipTierController;
use App\Http\Controllers\Api\Admin\TelegramRegistrationController;
use App\Http\Controllers\Api\Admin\PaymentVerificationController;
use App\Http\Controllers\Api\Admin\UserLifecycleController;
use App\Http\Controllers\Api\MembershipVerificationController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/public/payments/{payment}/receipt', [PaymentVerificationController::class, 'publicReceipt'])
    ->middleware('signed')
    ->name('api.public.payments.receipt');

Route::get('/verify-membership', [MembershipVerificationController::class, 'verify'])
    ->name('api.verify-membership');

// Internal API for n8n automations (no session/CSRF — the api middleware group is stateless).
Route::post('/horarios/enviar-recordatorios', [ScheduleController::class, 'sendReminders'])
    ->middleware('internal_api_token')
    ->name('api.schedules.send-reminders');

Route::prefix('admin')
    ->middleware(['internal_api_token'])
    ->group(function (): void {
        Route::get('/affiliate-tree', [AffiliateTreeController::class, 'index']);
        Route::get('/affiliate-tree/{user}', [AffiliateTreeController::class, 'show']);

        Route::post('/financial-stats/register', [FinancialStatsController::class, 'registerForDate']);
        Route::post('/financial-stats/register-today', [FinancialStatsController::class, 'registerToday']);
        Route::post('/financial-stats/register-yesterday', [FinancialStatsController::class, 'registerYesterday']);
        Route::post('/financial-stats/register-range', [FinancialStatsController::class, 'registerRange']);
        Route::get('/financial-stats/{date}', [FinancialStatsController::class, 'statsByDate'])
            ->where('date', '\\d{4}-\\d{2}-\\d{2}');
        Route::get('/financial-stats/dashboard', [FinancialStatsController::class, 'dashboard']);

        Route::post('/memberships/recalculate-tiers', [MembershipTierController::class, 'recalculate']);
        Route::get('/memberships/recalculate-tiers/{user}', [MembershipTierController::class, 'show']);
        Route::get('/memberships/expired-today', [MembershipTierController::class, 'expiredToday'])
            ->name('api.admin.memberships.expired-today');

        Route::post('/users/prune-inactive', [UserLifecycleController::class, 'pruneInactive'])
            ->name('api.admin.users.prune-inactive');

        Route::post('/groups/whatsapp/remove-free', [GroupController::class, 'removeFreeMembersWhatsapp'])
            ->name('api.admin.groups.whatsapp.remove-free');

        Route::post('/groups/telegram/remove-free', [GroupController::class, 'removeFreeMembersTelegram'])
            ->name('api.admin.groups.telegram.remove-free');

        Route::post('/telegram/register-chat-id', [TelegramRegistrationController::class, 'registerChatId'])
            ->name('api.admin.telegram.register-chat-id');

        Route::get('/payments/pending', [PaymentVerificationController::class, 'pending'])
            ->name('api.admin.payments.pending.list');
        Route::get('/payments/pending/{payment}', [PaymentVerificationController::class, 'show'])
            ->name('api.admin.payments.pending.show');
        Route::get('/payments/pending/{payment}/receipt', [PaymentVerificationController::class, 'receipt'])
            ->name('api.admin.payments.pending.receipt');
        Route::post('/payments/pending/{payment}/approve', [PaymentVerificationController::class, 'approve'])
            ->name('api.admin.payments.pending.approve');
        Route::post('/payments/pending/{payment}/reject', [PaymentVerificationController::class, 'reject'])
            ->name('api.admin.payments.pending.reject');

        // Legacy-compatible aliases for existing n8n flows.
        Route::prefix('/v2/payments/n8n/recargas')->group(function (): void {
            Route::get('/{payment}', [PaymentVerificationController::class, 'show'])
                ->name('api.v2.payments.n8n.recargas.show');
            Route::get('/{payment}/comprobante', [PaymentVerificationController::class, 'receipt'])
                ->name('api.v2.payments.n8n.recargas.comprobante');
            Route::post('/{payment}/aprobar', [PaymentVerificationController::class, 'approve'])
                ->name('api.v2.payments.n8n.recargas.aprobar');
            Route::post('/{payment}/rechazar', [PaymentVerificationController::class, 'reject'])
                ->name('api.v2.payments.n8n.recargas.rechazar');
        });
    });
