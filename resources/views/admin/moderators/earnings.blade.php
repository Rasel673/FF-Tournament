@extends('admin.layout')
@section('title', 'My Earnings & Withdraw')
@section('heading', 'Moderator Commission & Earnings')

@section('content')
<div class="space-y-6">
    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Available Balance -->
        <div class="rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 text-white shadow-md shadow-emerald-500/20">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-bold tracking-wider opacity-80">Withdrawable Balance</span>
                <span class="text-2xl">💰</span>
            </div>
            <div class="text-3xl font-extrabold mt-2">৳{{ number_format($user->moderator_balance ?? 0) }}</div>
            <div class="text-xs opacity-80 mt-1">Available for cashout</div>
        </div>

        <!-- Total Earned -->
        <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-xs uppercase font-bold tracking-wider">Total Commission</span>
                <span class="text-xl text-brand">📈</span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 mt-2">৳{{ number_format($totalCommissionEarned) }}</div>
            <div class="text-xs text-slate-500 mt-1">Lifetime earnings</div>
        </div>

        <!-- Completed Matches -->
        <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-xs uppercase font-bold tracking-wider">Completed Matches</span>
                <span class="text-xl text-blue-500">🎮</span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 mt-2">{{ $completedTournamentsCount }}</div>
            <div class="text-xs text-slate-500 mt-1">Tournaments arranged</div>
        </div>

        <!-- Commission Rate -->
        <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between text-slate-500">
                <span class="text-xs uppercase font-bold tracking-wider">Commission Rate</span>
                <span class="text-xl text-amber-500">⚡</span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 mt-2">৳{{ $user->moderator_commission_rate ?? \App\Models\Setting::get('default_moderator_commission', 50) }}</div>
            <div class="text-xs text-slate-500 mt-1">Per completed match</div>
        </div>
    </div>

    <!-- Withdrawal Request Form -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm p-5 sm:p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="h-10 w-10 rounded-xl bg-orange-100 text-brand flex items-center justify-center font-bold text-lg">
                💸
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-base">Request Withdrawal</h3>
                <p class="text-xs text-slate-500">Submit request to admin to withdraw your earned tournament commission</p>
            </div>
        </div>

        @if (($user->moderator_balance ?? 0) < 10)
            <div class="rounded-xl bg-amber-50 border border-amber-200 p-3.5 text-xs text-amber-800 flex items-center gap-2">
                <span>⚠️ Minimum withdrawal amount is ৳10. Your current balance is ৳{{ $user->moderator_balance ?? 0 }}.</span>
            </div>
        @else
            <form method="POST" action="{{ route('admin.moderator.withdraw') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                @csrf
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Method</label>
                    <select name="method" required class="w-full rounded-xl bg-white border border-slate-300 px-3.5 py-2.5 text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand shadow-sm">
                        <option value="">-- Select Payment Method --</option>
                        @forelse ($paymentMethods as $pm)
                            <option value="{{ $pm->name }}">{{ $pm->name }}</option>
                        @empty
                            <option value="bKash">bKash</option>
                            <option value="Nagad">Nagad</option>
                            <option value="Rocket">Rocket</option>
                        @endforelse
                    </select>
                </div>
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Account Number</label>
                    <input type="text" name="account_number" required placeholder="01XXXXXXXXX"
                           class="w-full rounded-xl bg-white border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand shadow-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Amount (BDT)</label>
                    <input type="number" min="10" max="{{ $user->moderator_balance ?? 0 }}" name="amount" value="{{ $user->moderator_balance ?? 0 }}" required
                           class="w-full rounded-xl bg-white border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand shadow-sm">
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold py-2.5 px-4 shadow-sm text-xs transition-all flex items-center justify-center gap-1.5">
                        <span>Submit Request</span>
                    </button>
                </div>
            </form>
        @endif
    </div>

    <!-- Dual History Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Withdrawal Requests History -->
        <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h4 class="font-bold text-slate-900 text-sm">Withdrawal Requests</h4>
                <span class="text-xs text-slate-500">History</span>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold">
                        <tr>
                            <th class="p-3 pl-4">Amount</th>
                            <th class="p-3">Method & Account</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 pr-4 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($withdrawals as $w)
                            <tr class="hover:bg-slate-50/70">
                                <td class="p-3 pl-4 font-bold text-slate-900">৳{{ number_format($w->amount) }}</td>
                                <td class="p-3">
                                    <div class="font-semibold text-slate-800">{{ $w->method }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono">{{ $w->account_number }}</div>
                                </td>
                                <td class="p-3">
                                    @if ($w->status === 'approved')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Approved</span>
                                    @elseif ($w->status === 'rejected')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Rejected</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Pending</span>
                                    @endif
                                </td>
                                <td class="p-3 pr-4 text-right text-slate-500">{{ $w->created_at->format('d M, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-slate-400">No withdrawal requests yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($withdrawals->hasPages())
                <div class="p-3 border-t border-slate-100">{{ $withdrawals->links() }}</div>
            @endif
        </div>

        <!-- Commission Credits History -->
        <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h4 class="font-bold text-slate-900 text-sm">Commission Credits</h4>
                <span class="text-xs text-slate-500">Earned Per Match</span>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold">
                        <tr>
                            <th class="p-3 pl-4">Match / Tournament</th>
                            <th class="p-3">Commission</th>
                            <th class="p-3 pr-4 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($commissions as $c)
                            <tr class="hover:bg-slate-50/70">
                                <td class="p-3 pl-4">
                                    <div class="font-semibold text-slate-900">{{ $c->tournament?->title ?? 'Tournament Match' }}</div>
                                    <div class="text-[10px] text-brand font-mono">{{ $c->tournament?->match_number }}</div>
                                </td>
                                <td class="p-3 font-bold text-emerald-600">+৳{{ number_format($c->amount) }}</td>
                                <td class="p-3 pr-4 text-right text-slate-500">{{ $c->created_at->format('d M, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-6 text-center text-slate-400">No match commissions credited yet. Complete matches to earn commission!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($commissions->hasPages())
                <div class="p-3 border-t border-slate-100">{{ $commissions->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
