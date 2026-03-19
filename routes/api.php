<?php

use App\Http\Controllers\Api\Admin\AffiliateTreeController;
use App\Http\Controllers\Api\Admin\FinancialStatsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['internal_api_token'])
    ->group(function (): void {
        Route::get('/affiliate-tree', [AffiliateTreeController::class, 'index']);
        Route::get('/affiliate-tree/{user}', [AffiliateTreeController::class, 'show']);

        Route::post('/financial-stats/register', [FinancialStatsController::class, 'registerForDate']);
        Route::post('/financial-stats/register-yesterday', [FinancialStatsController::class, 'registerYesterday']);
        Route::get('/financial-stats/dashboard', [FinancialStatsController::class, 'dashboard']);
    });
