@extends('admin.layout')
@section('title', $user->name)
@section('heading', $user->name)

@section('content')
    @php
        $input = 'rounded-xl bg-white border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand';
    @endphp

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="space-y-6">
            <!-- Player Details Card -->
            <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-6 space-y-3.5 text-sm">
                @if ($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" class="h-20 w-20 rounded-2xl object-cover border border-zinc-200 shadow-sm" alt="avatar">
                @endif
                <div><span class="text-zinc-500 font-medium">UID:</span> <span class="font-mono text-zinc-800 font-semibold">{{ $user->uid }}</span></div>
                <div><span class="text-zinc-500 font-medium">Phone:</span> <span class="text-zinc-800 font-semibold">{{ $user->phone }}</span></div>
                <div><span class="text-zinc-500 font-medium">Balance:</span> <b class="text-zinc-900 text-lg">{{ number_format($user->balance) }} BDT</b></div>
                <div>
                    <span class="text-zinc-500 font-medium">Status:</span>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold ml-1 {{ $user->is_blocked ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                        {{ $user->is_blocked ? 'Blocked' : 'Active' }}
                    </span>
                </div>
                <form method="POST" action="{{ route('admin.users.toggle-block', $user) }}" class="pt-3 border-t border-zinc-100">
                    @csrf
                    <button class="w-full rounded-xl px-4 py-2 text-white font-semibold text-xs shadow-sm transition-all {{ $user->is_blocked ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700' }}">
                        {{ $user->is_blocked ? 'Unblock Player' : 'Block Player' }}
                    </button>
                </form>
            </div>

            <!-- Adjust Balance Card -->
            <form method="POST" action="{{ route('admin.users.adjust-balance', $user) }}" class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-6 space-y-3.5 text-sm">
                @csrf
                <div class="font-bold text-zinc-900 text-base">Adjust Balance</div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Adjustment Type</label>
                    <select name="type" class="{{ $input }} w-full">
                        <option value="bonus">Add bonus (+)</option>
                        <option value="penalty">Deduct (−)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Amount (BDT)</label>
                    <input type="number" min="1" name="amount" placeholder="e.g. 100" required class="{{ $input }} w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Reason / Note</label>
                    <input name="note" placeholder="Note (optional)" class="{{ $input }} w-full">
                </div>
                <button class="w-full rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold py-2.5 shadow-sm shadow-orange-500/20 transition-all">Apply Adjustment</button>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <!-- Transactions Table -->
            <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-zinc-100 font-bold text-zinc-900 text-sm">Recent Transactions</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-zinc-50 text-zinc-600 text-xs uppercase font-semibold border-b border-zinc-200 tracking-wider">
                            <tr>
                                <th class="p-3.5 pl-5">Title</th>
                                <th class="p-3.5">Amount</th>
                                <th class="p-3.5">Status</th>
                                <th class="p-3.5 pr-5">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                        @forelse ($transactions as $tx)
                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                <td class="p-3.5 pl-5 font-semibold text-zinc-900">{{ $tx->title }}</td>
                                <td class="p-3.5 font-bold {{ $tx->signed_amount >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $tx->signed_amount > 0 ? '+' : '' }}{{ $tx->signed_amount }} BDT
                                </td>
                                <td class="p-3.5">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium bg-zinc-100 text-zinc-700">
                                        {{ ucfirst($tx->status) }}
                                    </span>
                                </td>
                                <td class="p-3.5 pr-5 text-zinc-500 text-xs">{{ $tx->created_at->format('d M, g:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="p-6 text-center text-zinc-500 font-medium">No transactions yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tournaments Table -->
            <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-zinc-100 font-bold text-zinc-900 text-sm">Joined Tournaments</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-zinc-50 text-zinc-600 text-xs uppercase font-semibold border-b border-zinc-200 tracking-wider">
                            <tr>
                                <th class="p-3.5 pl-5">Tournament</th>
                                <th class="p-3.5">Status</th>
                                <th class="p-3.5 pr-5">Registered</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                        @forelse ($registrations as $reg)
                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                <td class="p-3.5 pl-5 font-semibold text-zinc-900">{{ $reg->tournament->title }}</td>
                                <td class="p-3.5">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium bg-zinc-100 text-zinc-700">
                                        {{ ucfirst($reg->tournament->status) }}
                                    </span>
                                </td>
                                <td class="p-3.5 pr-5 text-zinc-500 text-xs">{{ $reg->created_at->format('d M, g:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="p-6 text-center text-zinc-500 font-medium">No tournaments joined yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
