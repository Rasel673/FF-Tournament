<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ModeratorController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PlayerHistoryController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\TournamentController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Middleware\EnsureIsModerator;
use Illuminate\Support\Facades\Route;

// Public
Route::post('send-otp', [AuthController::class, 'sendOtp'])->middleware('throttle:5,1');
Route::post('resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:5,1');
Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Pictures & Banners API (Multiple pictures/banners)
Route::get('banners', [BannerController::class, 'index']);
Route::get('banners/{banner}', [BannerController::class, 'show']);
Route::post('banners', [BannerController::class, 'store']);
Route::get('pictures', [BannerController::class, 'index']);
Route::get('photo', [BannerController::class, 'index']);
Route::post('photo', [BannerController::class, 'store']);

// Notifications API
Route::get('notifications', [NotificationController::class, 'index']);

// Categories API
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{id}', [CategoryController::class, 'show']);
Route::get('categories/{id}/tournaments', [CategoryController::class, 'tournaments']);

// Public Tournaments / Matches
Route::get('tournaments', [TournamentController::class, 'index']);
Route::get('tournaments/{id}', [TournamentController::class, 'show']);

// Needs header:  Authorization: Bearer <token>
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('home', [HomeController::class, 'index']);

    Route::get('categories/{id}/tournaments', [CategoryController::class, 'tournaments']);

    Route::get('tournaments', [TournamentController::class, 'index']);
    Route::get('tournaments/{id}', [TournamentController::class, 'show']);
    Route::post('tournaments/{id}/join', [TournamentController::class, 'join']);

    Route::get('wallet', [WalletController::class, 'index']);
    Route::post('wallet/deposit', [WalletController::class, 'deposit']);
    Route::post('wallet/withdraw', [WalletController::class, 'withdraw']);
    Route::get('transactions', [TransactionController::class, 'index']);
    Route::get('player-history', [PlayerHistoryController::class, 'index']);

    Route::get('profile', [ProfileController::class, 'show']);
    Route::post('profile', [ProfileController::class, 'update']);
    Route::get('support', [SupportController::class, 'index']);

    // Moderator Application & Status
    Route::post('moderator/apply', [ModeratorController::class, 'apply']);
    Route::get('moderator/status', [ModeratorController::class, 'status']);

    // Moderator & Admin Tournament Result Operations
    Route::post('tournaments/{id}/approve-results', [ModeratorController::class, 'approveResults']);

    // Moderator Privileged Operations
    Route::middleware(EnsureIsModerator::class)->prefix('moderator')->group(function () {
        Route::get('tournaments', [ModeratorController::class, 'tournaments']);
        Route::post('tournaments', [ModeratorController::class, 'storeTournament']);
        Route::put('tournaments/{id}/room', [ModeratorController::class, 'updateRoomDetails']);
        Route::post('tournaments/{id}/results', [ModeratorController::class, 'storeResults']);
        Route::get('earnings', [ModeratorController::class, 'earnings']);
        Route::post('withdraw', [ModeratorController::class, 'withdraw']);
    });

});
