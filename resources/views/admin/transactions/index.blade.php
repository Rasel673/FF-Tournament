@extends('admin.layout')
@section('title', 'Deposits & Withdrawals')
@section('heading', 'Deposits & Withdrawals')

@section('content')
    <form method="GET" class="flex flex-wrap gap-2 mb-4 text-sm">
        <select name="type" class="rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2">
            <option value="">All types</option>
            @foreach (['deposit' => 'Deposit', 'withdraw' => 'Withdraw', 'tournament_entry' => 'Tournament entry', 'prize' => 'Prize', 'bonus' => 'Bonus', 'penalty' => 'Deduction'] as $v => $l)
                <option value="{{ $v }}" @selected(request('type') === $v)>{{ $l }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2">
            <option value="">All statuses</option>
            @foreach (['pending', 'approved', 'rejected'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="rounded-lg bg-orange-600 hover:bg-orange-700 text-white px-4 py-2">Filter</button>
    </form>

    <div class="rounded-xl bg-zinc-900 border border-zinc-800 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-zinc-400 text-left"><tr>
                <th class="p-3">Player</th><th class="p-3">Type</th><th class="p-3">Method</th><th class="p-3">Amount</th>
                <th class="p-3">TrxID / Account</th><th class="p-3">Proof</th><th class="p-3">Status</th><th class="p-3">When</th><th class="p-3"></th>
            </tr></thead>
            <tbody>
            @forelse ($transactions as $tx)
                <tr class="border-t border-zinc-800 align-top">
                    <td class="p-3"><a href="{{ route('admin.users.show', $tx->user_id) }}" class="hover:underline">{{ $tx->user->name }}</a></td>
                    <td class="p-3">{{ $tx->title }}</td>
                    <td class="p-3">{{ $tx->method ?? '-' }}</td>
                    <td class="p-3 {{ $tx->signed_amount >= 0 ? 'text-green-400' : 'text-red-400' }}">{{ $tx->signed_amount > 0 ? '+' : '' }}{{ $tx->signed_amount }}</td>
                    <td class="p-3">{{ $tx->trx_id ?? $tx->account_number ?? '-' }}</td>
                    <td class="p-3">
                        @if ($tx->screenshot)
                            <a href="{{ $tx->screenshot_url }}" target="_blank" class="text-orange-500 hover:underline">View</a>
                        @else - @endif
                    </td>
                    <td class="p-3">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ ['pending' => 'bg-yellow-900 text-yellow-300', 'approved' => 'bg-green-900 text-green-300', 'rejected' => 'bg-red-900 text-red-300'][$tx->status] }}">{{ ucfirst($tx->status) }}</span>
                    </td>
                    <td class="p-3 whitespace-nowrap text-zinc-400">{{ $tx->created_at->format('d M, g:i A') }}</td>
                    <td class="p-3">
                        @if ($tx->status === 'pending' && in_array($tx->type, ['deposit', 'withdraw']))
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.transactions.approve', $tx) }}" onsubmit="return confirm('Approve this request?')">
                                    @csrf <button class="rounded bg-green-700 hover:bg-green-600 px-3 py-1 text-white">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.transactions.reject', $tx) }}" onsubmit="this.note.value = prompt('Reason (optional)') ?? ''; return confirm('Reject this request?')">
                                    @csrf <input type="hidden" name="note">
                                    <button class="rounded bg-red-800 hover:bg-red-700 px-3 py-1 text-white">Reject</button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="p-6 text-center text-zinc-500">No transactions found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $transactions->links() }}</div>
@endsection
