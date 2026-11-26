<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ModeratorController as AdminModeratorController;
use App\Http\Controllers\Admin\ModeratorEarningsController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TournamentController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\AdminOnly;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::any('ff-tournament', function () {
    return redirect()->route('admin.dashboard');
});

Route::any('ff-tournament/{any}', function ($any) {
    return redirect('/ff-tournament/public/' . $any);
})->where('any', '.*');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');

    // Accessible by both Admin and Moderator
    Route::middleware(IsAdmin::class)->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::put('password', [AuthController::class, 'updatePassword'])->name('password.update');

        // Tournaments (scoped to assigned tournaments for moderators)
        Route::resource('tournaments', TournamentController::class)->except('show');
        Route::get('tournaments/{tournament}/results', [TournamentController::class, 'results'])->name('tournaments.results');
        Route::post('tournaments/{tournament}/results', [TournamentController::class, 'storeResults'])->name('tournaments.results.store');

        // Moderator Earnings & Cashout
        Route::get('moderator/earnings', [ModeratorEarningsController::class, 'index'])->name('moderator.earnings');
        Route::post('moderator/withdraw', [ModeratorEarningsController::class, 'withdraw'])->name('moderator.withdraw');

        // Strictly Admin Only (blocked & redirected for moderators)
        Route::middleware(AdminOnly::class)->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

            // Tournament Result Approvals & Rejections (Admin Only)
            Route::post('tournaments/{tournament}/approve-results', [TournamentController::class, 'approveResults'])->name('tournaments.approve-results');
            Route::post('tournaments/{tournament}/reject-results', [TournamentController::class, 'rejectResults'])->name('tournaments.reject-results');

            Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::post('categories/{category}/toggle', [CategoryController::class, 'toggleActive'])->name('categories.toggle');

            Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
            Route::post('transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
            Route::post('transactions/{transaction}/reject', [TransactionController::class, 'reject'])->name('transactions.reject');

            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
            Route::post('users/{user}/toggle-block', [UserController::class, 'toggleBlock'])->name('users.toggle-block');
            Route::post('users/{user}/adjust-balance', [UserController::class, 'adjustBalance'])->name('users.adjust-balance');

            Route::get('payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');
            Route::post('payment-methods', [PaymentMethodController::class, 'store'])->name('payment-methods.store');
            Route::put('payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('payment-methods.update');
            Route::delete('payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');

            Route::resource('banners', BannerController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::post('banners/{banner}/toggle', [BannerController::class, 'toggleActive'])->name('banners.toggle');
            Route::redirect('photo', 'banners');

            Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
            Route::post('notifications', [AdminNotificationController::class, 'store'])->name('notifications.store');
            Route::delete('notifications/{notification}', [AdminNotificationController::class, 'destroy'])->name('notifications.destroy');

            Route::get('moderators', [AdminModeratorController::class, 'index'])->name('moderators.index');
            Route::post('moderators', [AdminModeratorController::class, 'store'])->name('moderators.store');
            Route::post('moderators/create-account', [AdminModeratorController::class, 'createAccount'])->name('moderators.create-account');
            Route::post('moderators/requests/{moderatorRequest}/approve', [AdminModeratorController::class, 'approveRequest'])->name('moderators.approve');
            Route::post('moderators/requests/{moderatorRequest}/reject', [AdminModeratorController::class, 'rejectRequest'])->name('moderators.reject');
            Route::post('moderators/{user}/revoke', [AdminModeratorController::class, 'revoke'])->name('moderators.revoke');
            Route::post('moderators/{user}/commission', [AdminModeratorController::class, 'updateCommission'])->name('moderators.update-commission');

            Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
        });
    });
});
