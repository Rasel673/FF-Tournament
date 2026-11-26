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
        // 1. Resolve method: could be ID (int) or name (string) under 'method', 'payment_method_id', or 'payment_method'
        $methodInput = $request->input('method') ?? $request->input('payment_method_id') ?? $request->input('payment_method');
        if (is_numeric($methodInput)) {
            $pm = PaymentMethod::find((int) $methodInput);
            if ($pm) {
                $request->merge(['method' => $pm->name]);
            }
        } elseif (is_string($methodInput) && !empty($methodInput)) {
            $pm = PaymentMethod::where('name', $methodInput)->first()
                ?? PaymentMethod::whereRaw('LOWER(name) = ?', [strtolower($methodInput)])->first();
            if ($pm) {
                $request->merge(['method' => $pm->name]);
            }
        }

        // 2. Resolve trx_id: could be 'trx', 'trx_id', 'transaction_id'
        $trxInput = $request->input('trx_id') ?? $request->input('trx') ?? $request->input('transaction_id');
        if (!empty($trxInput)) {
            $request->merge(['trx_id' => (string) $trxInput]);
        }

        // 3. Resolve account_number / sender_number
        $accountInput = $request->input('account_number') ?? $request->input('sender_number');
        if (!empty($accountInput)) {
            $request->merge(['account_number' => (string) $accountInput]);
        }

        // 4. Handle screenshot: if empty string or not an uploaded file, clear it so image validator doesn't reject string
        if (!$request->hasFile('screenshot')) {
            $request->request->remove('screenshot');
        }

        $data = $request->validate([
            'amount' => 'required|integer|min:'.(int) Setting::get('min_deposit', 10),
            'method' => ['required', Rule::exists('payment_methods', 'name')->where('is_active', true)],
            'trx_id' => ['required', 'string', 'max:50', Rule::unique('transactions', 'trx_id')->where('type', 'deposit')],
            'account_number' => 'nullable|string|max:30',
            'screenshot' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $screenshotPath = $request->hasFile('screenshot')
            ? $request->file('screenshot')->store('deposits', 'public')
            : null;

        $transaction = $request->user()->transactions()->create([
            'type' => 'deposit',
            'amount' => $data['amount'],
            'method' => $data['method'],
            'trx_id' => $data['trx_id'],
            'account_number' => $data['account_number'] ?? null,
            'screenshot' => $screenshotPath,
            'status' => 'pending',
        ]);

        return (new TransactionResource($transaction))->response()->setStatusCode(201);
    }

    public function withdraw(Request $request)
    {
        $methodInput = $request->input('method') ?? $request->input('payment_method_id') ?? $request->input('payment_method');
        if (is_numeric($methodInput)) {
            $pm = PaymentMethod::find((int) $methodInput);
            if ($pm) {
                $request->merge(['method' => $pm->name]);
            }
        } elseif (is_string($methodInput) && !empty($methodInput)) {
            $pm = PaymentMethod::where('name', $methodInput)->first()
                ?? PaymentMethod::whereRaw('LOWER(name) = ?', [strtolower($methodInput)])->first();
            if ($pm) {
                $request->merge(['method' => $pm->name]);
            }
        }

        $accountInput = $request->input('account_number') ?? $request->input('sender_number') ?? $request->input('receiver_number');
        if (!empty($accountInput)) {
            $request->merge(['account_number' => (string) $accountInput]);
        }

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
