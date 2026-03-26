<?php

use App\Http\Controllers\Api\Admin\AffiliateTreeController;
use App\Http\Controllers\Api\Admin\FinancialStatsController;
use App\Http\Controllers\Api\Admin\MembershipTierController;
use Illuminate\Support\Facades\Route;

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
    });
