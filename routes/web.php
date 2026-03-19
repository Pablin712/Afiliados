<?php

use App\Http\Controllers\ActionController;
use App\Http\Controllers\Admin\FinancialDashboardController;
use App\Http\Controllers\Admin\ProfitsController;
use App\Http\Controllers\Admin\UsersTreeController;
use App\Http\Controllers\MembershipsController;
use App\Http\Controllers\MembershipTypeController;
use App\Http\Controllers\PendingRegistrationController;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Language switcher route
Route::get('/locale/{locale}', function ($locale) {
    $availableLocales = ['en', 'es'];
    if (in_array($locale, $availableLocales)) {
        session(['locale' => $locale]);
        \Illuminate\Support\Facades\App::setLocale($locale);
        cookie()->queue('locale', $locale, 60 * 24 * 365); // 1 year
    }
    return redirect()->back();
})->name('locale.change');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/plans', [PlansController::class, 'index'])
        ->middleware('verified')
        ->name('plans.index');

    Route::post('/plans/payment', [PlansController::class, 'store'])
        ->middleware('verified')
        ->name('plans.payment.store');

    Route::post('/plans/programs', [ProgramController::class, 'store'])
        ->middleware(['verified', 'role:admin'])
        ->name('plans.programs.store');

    Route::put('/plans/programs/{program}', [ProgramController::class, 'update'])
        ->middleware(['verified', 'role:admin'])
        ->name('plans.programs.update');

    Route::get('/actions', [ActionController::class, 'index'])
        ->middleware(['verified', 'permission:view actions'])
        ->name('actions.index');

    Route::get('/memberships', [MembershipsController::class, 'index'])
        ->middleware(['verified', 'permission:view memberships'])
        ->name('memberships.index');

    Route::get('/membership-types', [MembershipTypeController::class, 'index'])
        ->middleware(['verified', 'permission:view membership_types'])
        ->name('membership-types.index');

    Route::get('/admin/pending-registrations', [PendingRegistrationController::class, 'index'])
        ->middleware(['verified', 'permission:manage payments'])
        ->name('admin.pending-registrations.index');

    Route::post('/admin/pending-registrations/{payment}/approve', [PendingRegistrationController::class, 'approve'])
        ->middleware(['verified', 'permission:manage payments'])
        ->name('admin.pending-registrations.approve');

    Route::post('/admin/pending-registrations/{payment}/reject', [PendingRegistrationController::class, 'reject'])
        ->middleware(['verified', 'permission:manage payments'])
        ->name('admin.pending-registrations.reject');

    Route::get('/admin/users-tree', [UsersTreeController::class, 'index'])
        ->middleware(['verified', 'permission:view users'])
        ->name('admin.users-tree.index');

    Route::get('/admin/users-tree/{user}/insights', [UsersTreeController::class, 'insights'])
        ->middleware(['verified', 'permission:view users'])
        ->name('admin.users-tree.insights');

    Route::get('/admin/financial-dashboard', [FinancialDashboardController::class, 'index'])
        ->middleware(['verified', 'permission:report profits'])
        ->name('admin.financial-dashboard.index');

    Route::post('/admin/financial-dashboard/register-today', [FinancialDashboardController::class, 'registerToday'])
        ->middleware(['verified', 'permission:manage profits'])
        ->name('admin.financial-dashboard.register-today');

    Route::post('/admin/financial-dashboard/register-yesterday', [FinancialDashboardController::class, 'registerYesterday'])
        ->middleware(['verified', 'permission:manage profits'])
        ->name('admin.financial-dashboard.register-yesterday');

    Route::get('/admin/profits', [ProfitsController::class, 'index'])
        ->middleware(['verified', 'permission:view profits'])
        ->name('admin.profits.index');

    Route::post('/admin/profits/{profit}/mark-as-paid', [ProfitsController::class, 'markAsPaid'])
        ->middleware(['verified', 'permission:manage profits'])
        ->name('admin.profits.mark-as-paid');

    Route::post('/membership-types', [MembershipTypeController::class, 'store'])
        ->middleware(['verified', 'permission:create membership_types'])
        ->name('membership-types.store');

    Route::put('/membership-types/{membershipType}', [MembershipTypeController::class, 'update'])
        ->middleware(['verified', 'permission:edit membership_types'])
        ->name('membership-types.update');

    Route::delete('/membership-types/{membershipType}', [MembershipTypeController::class, 'destroy'])
        ->middleware(['verified', 'permission:delete membership_types'])
        ->name('membership-types.destroy');
});

require __DIR__.'/auth.php';
