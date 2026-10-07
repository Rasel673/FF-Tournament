<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\TournamentController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Needs header:  Authorization: Bearer <token>
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('home', [HomeController::class, 'index']);

    Route::get('tournaments', [TournamentController::class, 'index']);
    Route::get('tournaments/{id}', [TournamentController::class, 'show']);
    Route::post('tournaments/{id}/join', [TournamentController::class, 'join']);

    Route::get('wallet', [WalletController::class, 'index']);
    Route::post('wallet/deposit', [WalletController::class, 'deposit']);
    Route::post('wallet/withdraw', [WalletController::class, 'withdraw']);
    Route::get('transactions', [TransactionController::class, 'index']);

    Route::get('profile', [ProfileController::class, 'show']);
    Route::post('profile', [ProfileController::class, 'update']);
    Route::get('support', [SupportController::class, 'index']);
});
