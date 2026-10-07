<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\TransactionResource;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WalletController extends Controller
{
    /** Wallet screen: balance + recent transactions + payment methods (with the number to send money to). */
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'balance' => $user->balance,
                'min_deposit' => (int) Setting::get('min_deposit', 10),
                'min_withdraw' => (int) Setting::get('min_withdraw', 50),
                'payment_methods' => PaymentMethodResource::collection(PaymentMethod::where('is_active', true)->get()),
                'recent_transactions' => TransactionResource::collection($user->transactions()->with('tournament')->limit(5)->get()),
            ],
        ]);
    }

    public function deposit(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|integer|min:'.(int) Setting::get('min_deposit', 10),
            'method' => ['required', Rule::exists('payment_methods', 'name')->where('is_active', true)],
            'trx_id' => ['required', 'string', 'max:50', Rule::unique('transactions', 'trx_id')->where('type', 'deposit')],
            'screenshot' => 'required|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $transaction = $request->user()->transactions()->create([
            'type' => 'deposit',
            'amount' => $data['amount'],
            'method' => $data['method'],
            'trx_id' => $data['trx_id'],
            'screenshot' => $request->file('screenshot')->store('deposits', 'public'),
            'status' => 'pending',
        ]);

        return (new TransactionResource($transaction))->response()->setStatusCode(201);
    }

    public function withdraw(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|integer|min:'.(int) Setting::get('min_withdraw', 50),
            'method' => ['required', Rule::exists('payment_methods', 'name')->where('is_active', true)],
            'account_number' => 'required|string|max:20',
        ]);

        // Money is held (deducted) right away; admin rejection refunds it.
        $transaction = DB::transaction(function () use ($request, $data) {
            $user = User::lockForUpdate()->findOrFail($request->user()->id);

            abort_if($user->balance < $data['amount'], 422, 'Insufficient balance.');

            $user->decrement('balance', $data['amount']);

            return $user->transactions()->create([
                'type' => 'withdraw',
                'amount' => $data['amount'],
                'method' => $data['method'],
                'account_number' => $data['account_number'],
                'status' => 'pending',
            ]);
        });

        return (new TransactionResource($transaction))->response()->setStatusCode(201);
    }
}
