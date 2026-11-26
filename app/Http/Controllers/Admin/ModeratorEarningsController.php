<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Tournament;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ModeratorEarningsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $completedTournamentsCount = Tournament::where('moderator_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $totalCommissionEarned = (int) $user->transactions()
            ->where('type', 'commission')
            ->where('status', 'approved')
            ->sum('amount');

        $commissions = $user->transactions()
            ->where('type', 'commission')
            ->with('tournament')
            ->latest()
            ->paginate(10, ['*'], 'commissions_page');

        $withdrawals = $user->transactions()
            ->where('type', 'moderator_withdraw')
            ->latest()
            ->paginate(10, ['*'], 'withdrawals_page');

        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('name')->get();

        return view('admin.moderators.earnings', [
            'user' => $user,
            'completedTournamentsCount' => $completedTournamentsCount,
            'totalCommissionEarned' => $totalCommissionEarned,
            'commissions' => $commissions,
            'withdrawals' => $withdrawals,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function withdraw(Request $request): RedirectResponse
    {
        $user = $request->user();
        $balance = (int) ($user->moderator_balance ?? 0);

        $data = $request->validate([
            'method' => 'required|string|max:50',
            'account_number' => 'required|string|max:50',
            'amount' => 'required|integer|min:10',
        ]);

        if ($data['amount'] > $balance) {
            return back()->withErrors(['amount' => 'Withdrawal amount exceeds your available commission balance.']);
        }

        DB::transaction(function () use ($user, $data) {
            $user->decrement('moderator_balance', $data['amount']);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'moderator_withdraw',
                'amount' => $data['amount'],
                'method' => $data['method'],
                'account_number' => $data['account_number'],
                'status' => 'pending',
                'note' => 'Moderator withdrawal request submitted from panel.',
            ]);
        });

        return back()->with('success', "Withdrawal request of ৳{$data['amount']} submitted successfully. Waiting for admin approval.");
    }
}
