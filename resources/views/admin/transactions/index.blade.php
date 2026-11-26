@extends('admin.layout')
@section('title', 'Deposits & Withdrawals')
@section('heading', 'Deposits & Withdrawals')

@section('content')
    <form method="GET" class="flex flex-wrap gap-2.5 mb-5 text-sm items-center">
        <select name="type" class="rounded-xl bg-white border border-zinc-300 px-3.5 py-2 text-zinc-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand font-medium">
            <option value="">All types</option>
            @foreach (['deposit' => 'Deposit', 'withdraw' => 'Withdraw', 'tournament_entry' => 'Tournament entry', 'prize' => 'Prize', 'bonus' => 'Bonus', 'penalty' => 'Deduction'] as $v => $l)
                <option value="{{ $v }}" @selected(request('type') === $v)>{{ $l }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-xl bg-white border border-zinc-300 px-3.5 py-2 text-zinc-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand font-medium">
            <option value="">All statuses</option>
            @foreach (['pending', 'approved', 'rejected'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="rounded-xl bg-brand hover:bg-brand-dark text-white px-5 py-2 font-semibold shadow-sm shadow-orange-500/20 transition-all">Filter</button>
    </form>

    <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-zinc-50 text-zinc-600 text-xs uppercase font-semibold border-b border-zinc-200 tracking-wider">
                    <tr>
                        <th class="p-3.5 pl-5">Player</th>
                        <th class="p-3.5">Type</th>
                        <th class="p-3.5">Method</th>
                        <th class="p-3.5">Amount</th>
                        <th class="p-3.5">TrxID / Account</th>
                        <th class="p-3.5">Proof</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5">When</th>
                        <th class="p-3.5 pr-5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                @forelse ($transactions as $tx)
                    <tr class="hover:bg-zinc-50/70 transition-colors align-top">
                        <td class="p-3.5 pl-5">
                            <a href="{{ route('admin.users.show', $tx->user_id) }}" class="font-semibold text-zinc-900 hover:text-brand transition-colors">
                                {{ $tx->user->name }}
                            </a>
                        </td>
                        <td class="p-3.5 font-medium text-zinc-700">{{ $tx->title }}</td>
                        <td class="p-3.5 font-medium text-zinc-700">{{ $tx->method ?? '-' }}</td>
                        <td class="p-3.5 font-bold {{ $tx->signed_amount >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $tx->signed_amount > 0 ? '+' : '' }}{{ $tx->signed_amount }}
                        </td>
                        <td class="p-3.5 font-mono text-xs text-zinc-600">{{ $tx->trx_id ?? $tx->account_number ?? '-' }}</td>
                        <td class="p-3.5">
                            @if ($tx->screenshot)
                                <a href="{{ $tx->screenshot_url }}" target="_blank" class="font-semibold text-brand hover:underline">View</a>
                            @else
                                <span class="text-zinc-400">-</span>
                            @endif
                        </td>
                        <td class="p-3.5">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ ['pending' => 'bg-amber-50 text-amber-700 border border-amber-200', 'approved' => 'bg-emerald-50 text-emerald-700 border border-emerald-200', 'rejected' => 'bg-rose-50 text-rose-700 border border-rose-200'][$tx->status] }}">
                                {{ ucfirst($tx->status) }}
                            </span>
                        </td>
                        <td class="p-3.5 whitespace-nowrap text-zinc-500 text-xs">{{ $tx->created_at->format('d M, g:i A') }}</td>
                        <td class="p-3.5 pr-5 text-right">
                            @if ($tx->status === 'pending' && in_array($tx->type, ['deposit', 'withdraw']))
                                <div class="flex items-center justify-end gap-1.5">
                                    <form method="POST" action="{{ route('admin.transactions.approve', $tx) }}" onsubmit="return confirm('Approve this request?')">
                                        @csrf
                                        <button class="rounded-lg bg-emerald-600 hover:bg-emerald-700 px-3 py-1 text-white text-xs font-semibold shadow-sm">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.transactions.reject', $tx) }}" onsubmit="this.note.value = prompt('Reason (optional)') ?? ''; return confirm('Reject this request?')">
                                        @csrf
                                        <input type="hidden" name="note">
                                        <button class="rounded-lg bg-rose-600 hover:bg-rose-700 px-3 py-1 text-white text-xs font-semibold shadow-sm">Reject</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-zinc-500 font-medium">No transactions found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $transactions->links() }}</div>
@endsection
