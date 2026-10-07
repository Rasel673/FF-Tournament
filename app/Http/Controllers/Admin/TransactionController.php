<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /** Deposit / withdraw requests. Filter: ?type=deposit|withdraw&status=pending|approved|rejected */
    public function index(Request $request)
    {
        $transactions = Transaction::with(['user', 'tournament'])
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.transactions.index', compact('transactions'));
    }

    public function approve(Transaction $transaction)
    {
        $this->review($transaction, 'approved');

        return back()->with('success', 'Request approved.');
    }

    public function reject(Request $request, Transaction $transaction)
    {
        $this->review($transaction, 'rejected', $request->input('note'));

        return back()->with('success', 'Request rejected.');
    }

    private function review(Transaction $transaction, string $status, ?string $note = null): void
    {
        DB::transaction(function () use ($transaction, $status, $note) {
            $tx = Transaction::lockForUpdate()->findOrFail($transaction->id);

            abort_if($tx->status !== 'pending', 422, 'This request was already reviewed.');
            abort_unless(in_array($tx->type, ['deposit', 'withdraw'], true), 422, 'Not a deposit or withdraw request.');

            $user = User::lockForUpdate()->findOrFail($tx->user_id);

            // Deposit approved -> add money. Withdraw rejected -> give the held money back.
            if ($tx->type === 'deposit' && $status === 'approved') {
                $user->increment('balance', $tx->amount);
            }
            if ($tx->type === 'withdraw' && $status === 'rejected') {
                $user->increment('balance', $tx->amount);
            }

            $tx->update(['status' => $status, 'note' => $note ?: $tx->note]);
        });
    }
}
