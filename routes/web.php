<?php

use App\Http\Controllers\ActionController;
use App\Http\Controllers\Admin\BanksController;
use App\Http\Controllers\Admin\MessageTemplatesController;
use App\Http\Controllers\Admin\CourseCatalogController;
use App\Http\Controllers\Auth\DeviceConflictController;
use App\Http\Controllers\Admin\UsersAdminController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\FinancialDashboardController;
use App\Http\Controllers\Admin\ProfitsController;
use App\Http\Controllers\Admin\UsersTreeController;
use App\Http\Controllers\MembershipsController;
use App\Http\Controllers\MembershipTypeController;
use App\Http\Controllers\PendingRegistrationController;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\ScannerDownloadController;
use App\Http\Controllers\User\AffiliateNetworkController;
use App\Http\Controllers\User\MyProfitsController;
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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/other-bank', [ProfileController::class, 'updateOtherBank'])->name('profile.other-bank.update');
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

    Route::post('/plans/card-checkout', [PlansController::class, 'initiateCardCheckout'])
        ->middleware('verified')
        ->name('plans.card-checkout');

    Route::get('/plans/card-payment/{payment}', [PlansController::class, 'showCardPayment'])
        ->middleware('verified')
        ->name('plans.card-payment');

    Route::get('/plans/card-return', [PlansController::class, 'cardPaymentReturn'])
        ->middleware('verified')
        ->name('plans.card-return');

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

    Route::put('/memberships/{id}', [MembershipsController::class, 'update'])
        ->middleware(['verified', 'permission:edit memberships'])
        ->name('memberships.update');

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

    Route::get('/admin/users', [UsersAdminController::class, 'index'])
        ->middleware(['verified', 'permission:view users'])
        ->name('admin.users.index');

    Route::get('/admin/users/search-sponsors', [UsersAdminController::class, 'searchSponsors'])
        ->middleware(['verified', 'permission:edit users'])
        ->name('admin.users.search-sponsors');

    Route::post('/admin/users/{user}/sponsor', [UsersAdminController::class, 'updateSponsor'])
        ->middleware(['verified', 'permission:edit users'])
        ->name('admin.users.update-sponsor');

    Route::get('/admin/financial-dashboard', [FinancialDashboardController::class, 'index'])
        ->middleware(['verified', 'permission:report profits'])
        ->name('admin.financial-dashboard.index');

    Route::get('/admin/banks', [BanksController::class, 'index'])
        ->middleware(['verified', 'permission:view banks'])
        ->name('admin.banks.index');

    Route::get('/admin/courses', [CourseCatalogController::class, 'index'])
        ->middleware(['verified', 'role:admin'])
        ->name('admin.courses.index');

    Route::post('/admin/courses/modules', [CourseCatalogController::class, 'storeModule'])
        ->middleware(['verified', 'role:admin'])
        ->name('admin.courses.modules.store');

    Route::patch('/admin/courses/modules/{module}/toggle-free', [CourseCatalogController::class, 'toggleFree'])
        ->middleware(['verified', 'role:admin'])
        ->name('admin.courses.modules.toggle-free');

    Route::delete('/admin/courses/modules/{module}', [CourseCatalogController::class, 'destroyModule'])
        ->middleware(['verified', 'role:admin'])
        ->name('admin.courses.modules.destroy');

    Route::post('/admin/courses/videos', [CourseCatalogController::class, 'storeVideo'])
        ->middleware(['verified', 'role:admin'])
        ->name('admin.courses.videos.store');

    Route::delete('/admin/courses/videos/{video}', [CourseCatalogController::class, 'destroyVideo'])
        ->middleware(['verified', 'role:admin'])
        ->name('admin.courses.videos.destroy');

    Route::post('/admin/courses/import-existing', [CourseCatalogController::class, 'importExisting'])
        ->middleware(['verified', 'role:admin'])
        ->name('admin.courses.import-existing');

    Route::get('/admin/message-templates', [MessageTemplatesController::class, 'index'])
        ->middleware(['verified', 'role:admin'])
        ->name('admin.message-templates.index');

    Route::put('/admin/message-templates/{messageTemplate}', [MessageTemplatesController::class, 'update'])
        ->middleware(['verified', 'role:admin'])
        ->name('admin.message-templates.update');

    Route::post('/admin/banks', [BanksController::class, 'store'])
        ->middleware(['verified', 'permission:create banks'])
        ->name('admin.banks.store');

    Route::put('/admin/banks/{bank}', [BanksController::class, 'update'])
        ->middleware(['verified', 'permission:edit banks'])
        ->name('admin.banks.update');

    Route::delete('/admin/banks/{bank}', [BanksController::class, 'destroy'])
        ->middleware(['verified', 'permission:delete banks'])
        ->name('admin.banks.destroy');

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

    Route::post('/admin/profits/users/{user}/mark-all-as-paid', [ProfitsController::class, 'markAllAsPaid'])
        ->middleware(['verified', 'permission:manage profits'])
        ->name('admin.profits.mark-all-as-paid');

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
