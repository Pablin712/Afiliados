<?php

use App\Http\Controllers\ActionController;
use App\Http\Controllers\MembershipsController;
use App\Http\Controllers\MembershipTypeController;
use App\Http\Controllers\PendingRegistrationController;
use App\Http\Controllers\PayphoneController;
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

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

// Device conflict (single-session enforcement)
Route::middleware(['auth'])->name('auth.device-conflict.')->prefix('auth/device-conflict')->group(function () {
    Route::get('/',         [DeviceConflictController::class, 'show'])->name('show');
    Route::post('/takeover', [DeviceConflictController::class, 'takeover'])->name('takeover');
    Route::get('/cancel',   [DeviceConflictController::class, 'cancel'])->name('cancel');
});

// Payphone redirects the browser here after card payment — no auth required (verified via API)
Route::get('/plans/payphone/callback', [PayphoneController::class, 'callback'])
    ->name('plans.payphone.callback');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/plans', [PlansController::class, 'index'])
        ->middleware('verified')
        ->name('plans.index');

    Route::get('/cursos', [CourseController::class, 'index'])
        ->middleware('verified')
        ->name('courses.index');

    Route::get('/cursos/videos/{video}/stream', [CourseController::class, 'stream'])
        ->middleware('verified')
        ->name('courses.videos.stream');

    Route::get('/mi-red', [AffiliateNetworkController::class, 'index'])
        ->middleware(['verified', 'role:user'])
        ->name('user.network.index');

    Route::get('/mi-red/{user}/insights', [AffiliateNetworkController::class, 'insights'])
        ->middleware(['verified', 'role:user'])
        ->name('user.network.insights');

    Route::get('/mis-ganancias', [MyProfitsController::class, 'index'])
        ->middleware(['verified', 'role:user'])
        ->name('user.profits.index');

    Route::post('/mis-ganancias/solicitar-cobro', [MyProfitsController::class, 'requestPayout'])
        ->middleware(['verified', 'role:user'])
        ->name('user.profits.request-payout');

    Route::prefix('/scanners')
        ->middleware(['verified', 'role:user'])
        ->name('scanners.')
        ->group(function (): void {
            Route::get('/deriv/open', [ScannerDownloadController::class, 'registerDerivAndRedirect'])
                ->name('deriv.redirect');

            Route::post('/prepare', [ScannerDownloadController::class, 'prepare'])
                ->name('prepare');

            Route::get('/download/{broker}/{pattern}', [ScannerDownloadController::class, 'download'])
                ->middleware('signed')
                ->name('download');
        });

    Route::post('/plans/payment', [PlansController::class, 'store'])
        ->middleware('verified')
        ->name('plans.payment.store');

    Route::get('/plans/payment', function () {
        return redirect()
            ->route('plans.index')
            ->with('error', __('messages.plans.invalid_payment_method'));
    })->middleware('verified');

    Route::post('/plans/payphone/prepare', [PayphoneController::class, 'prepare'])
        ->middleware('verified')
        ->name('plans.payphone.prepare');

    Route::post('/plans/renew-free', [PlansController::class, 'renewForFree'])
        ->middleware('verified')
        ->name('plans.renew-free');

    Route::get('/plans/renew-free', function () {
        return redirect()
            ->route('plans.index')
            ->with('error', __('messages.plans.invalid_payment_method'));
    })->middleware('verified');

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
