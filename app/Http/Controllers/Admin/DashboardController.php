<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\Transaction;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'stats' => [
                'Players' => User::where('is_admin', false)->count(),
                'Active tournaments' => Tournament::where('status', 'active')->count(),
                'Pending deposits' => Transaction::where('type', 'deposit')->where('status', 'pending')->count(),
                'Pending withdrawals' => Transaction::where('type', 'withdraw')->where('status', 'pending')->count(),
                'Total deposited (BDT)' => Transaction::where('type', 'deposit')->where('status', 'approved')->sum('amount'),
                'Total wallet balance (BDT)' => User::where('is_admin', false)->sum('balance'),
            ],
            'pending' => Transaction::with('user')
                ->whereIn('type', ['deposit', 'withdraw'])
                ->where('status', 'pending')
                ->latest()->limit(8)->get(),
        ]);
    }
}
