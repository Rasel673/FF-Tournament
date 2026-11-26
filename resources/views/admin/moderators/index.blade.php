@extends('admin.layout')
@section('title', 'Moderators Management')
@section('heading', 'Moderators & Requests')

@section('content')
<div class="space-y-6">
    <!-- Info Bar -->
    <div class="rounded-2xl bg-gradient-to-r from-blue-500/10 via-indigo-500/10 to-transparent border border-blue-200/80 p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3.5">
            <div class="h-12 w-12 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20 shrink-0 text-xl">
                🛡️
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="font-bold text-zinc-900 text-base">Tournament Moderators System</h2>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                        Commission: {{ \App\Models\Setting::get('default_moderator_commission', 50) }} BDT / Match
                    </span>
                </div>
                <p class="text-xs text-zinc-600 mt-0.5">
                    Moderators arrange matches, enter Room ID & Passwords, distribute prizes, and earn commissions. Players must play minimum {{ \App\Models\Setting::get('min_tournaments_for_moderator', 5) }} tournaments to apply.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.settings.edit') }}" class="px-3 py-1.5 rounded-xl bg-white border border-zinc-200 text-zinc-700 font-semibold hover:border-zinc-300 hover:bg-zinc-50 transition-colors shadow-sm text-xs inline-flex items-center gap-1.5">
                ⚙️ Commission Settings
            </a>
        </div>
    </div>

    <!-- Create New Moderator Account Card -->
    <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-5 sm:p-6">
        <div class="flex items-center gap-3 mb-3">
            <div class="h-10 w-10 rounded-xl bg-orange-100 text-brand flex items-center justify-center font-bold text-lg">
                ➕
            </div>
            <div>
                <h3 class="font-bold text-zinc-900 text-base">Create New Moderator Account</h3>
                <p class="text-xs text-zinc-500">Create an account for a new moderator. They will use their phone number and password to log in to this admin panel.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.moderators.create-account') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            @csrf
            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="e.g. Shakib Hasan"
                       class="w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2.5 text-xs text-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand shadow-sm">
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Phone Number (Login ID) <span class="text-rose-500">*</span></label>
                <input type="text" name="phone" required placeholder="018XXXXXXXX"
                       class="w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2.5 text-xs text-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand shadow-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required minlength="6" placeholder="Min 6 characters"
                       class="w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2.5 text-xs text-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand shadow-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Commission (BDT)</label>
                <input type="number" min="0" name="moderator_commission_rate" placeholder="Default: {{ \App\Models\Setting::get('default_moderator_commission', 50) }}"
                       class="w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2.5 text-xs text-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand shadow-sm">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-brand via-orange-600 to-amber-500 hover:from-brand-dark hover:to-orange-600 text-white font-bold py-2.5 px-4 shadow-sm text-xs transition-all flex items-center justify-center gap-1.5">
                    <span>➕ Create Moderator</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Quick Add Moderator Card (Promote Existing) -->
    <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-5 sm:p-6">
        <h3 class="font-bold text-zinc-900 text-base mb-1">Promote Existing Player to Moderator</h3>
        <p class="text-xs text-zinc-500 mb-4">Admins can promote any player directly to Moderator without requiring 5 tournament plays.</p>

        <form method="POST" action="{{ route('admin.moderators.store') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            @csrf
            <div class="md:col-span-7">
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Select Player</label>
                <select name="user_id" required class="w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2.5 text-xs font-medium text-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand shadow-sm">
                    <option value="">-- Choose a Player to Promote --</option>
                    @foreach ($availableUsers as $user)
                        <option value="{{ $user->id }}">
                            #{{ $user->id }} - {{ $user->name }} (UID: {{ $user->uid }} | {{ $user->phone }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-zinc-700 mb-1">Custom Commission (BDT, optional)</label>
                <input type="number" min="0" name="moderator_commission_rate" placeholder="Default: {{ \App\Models\Setting::get('default_moderator_commission', 50) }}"
                       class="w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2.5 text-xs text-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand shadow-sm">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-2.5 px-4 shadow-sm text-xs transition-all flex items-center justify-center gap-1.5">
                    <span>🛡️ Promote</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Pending Applications Section -->
    <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-zinc-900 text-base">Player Moderator Applications</h3>
                <p class="text-xs text-zinc-500">Applications submitted by players who played 5+ tournaments</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                Pending: {{ $requests->where('status', 'pending')->count() }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 border-b border-zinc-200 text-xs font-semibold text-zinc-600 uppercase tracking-wider">
                    <tr>
                        <th class="p-3.5 pl-5">Player</th>
                        <th class="p-3.5">Tournaments Played</th>
                        <th class="p-3.5">Note</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5">Date</th>
                        <th class="p-3.5 pr-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($requests as $req)
                        <tr class="hover:bg-zinc-50/70 transition-colors">
                            <td class="p-3.5 pl-5">
                                <a href="{{ route('admin.users.show', $req->user_id) }}" class="font-bold text-zinc-900 hover:text-brand transition-colors block">
                                    {{ $req->user->name }}
                                </a>
                                <div class="text-[11px] text-zinc-500 font-mono">UID: {{ $req->user->uid }} • {{ $req->phone }}</div>
                            </td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $req->tournaments_played >= 5 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-zinc-100 text-zinc-700' }}">
                                    🎮 {{ $req->tournaments_played }} Played
                                </span>
                            </td>
                            <td class="p-3.5 text-xs text-zinc-600 max-w-xs truncate">
                                {{ $req->note ?: 'No note provided' }}
                            </td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ [
                                    'pending' => 'bg-amber-100 text-amber-800 border border-amber-200',
                                    'approved' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                                    'rejected' => 'bg-rose-100 text-rose-800 border border-rose-200',
                                ][$req->status] ?? 'bg-zinc-100 text-zinc-700' }}">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </td>
                            <td class="p-3.5 text-xs text-zinc-500">
                                {{ $req->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="p-3.5 pr-5 text-right space-x-2">
                                @if ($req->status === 'pending')
                                    <form method="POST" action="{{ route('admin.moderators.approve', $req) }}" class="inline-block">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition-colors">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.moderators.reject', $req) }}" class="inline-block" onsubmit="return confirm('Reject this application?')">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition-colors">
                                            ✕ Reject
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-zinc-400">Processed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-zinc-400 text-sm">
                                📭 No moderator applications yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($requests->hasPages())
            <div class="p-4 border-t border-zinc-100">{{ $requests->links() }}</div>
        @endif
    </div>

    <!-- Active Moderators List -->
    <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-zinc-900 text-base">Active Moderators</h3>
                <p class="text-xs text-zinc-500">All users currently privileged to arrange matches & manage rooms</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                Total: {{ $moderators->total() }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 border-b border-zinc-200 text-xs font-semibold text-zinc-600 uppercase tracking-wider">
                    <tr>
                        <th class="p-3.5 pl-5">Moderator</th>
                        <th class="p-3.5">Matches Moderated</th>
                        <th class="p-3.5">Commission Balance</th>
                        <th class="p-3.5">Player Wallet</th>
                        <th class="p-3.5 pr-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($moderators as $mod)
                        <tr class="hover:bg-zinc-50/70 transition-colors">
                            <td class="p-3.5 pl-5">
                                <a href="{{ route('admin.users.show', $mod) }}" class="font-bold text-zinc-900 hover:text-brand transition-colors block">
                                    {{ $mod->name }}
                                </a>
                                <div class="text-[11px] text-zinc-500 font-mono">UID: {{ $mod->uid }} • {{ $mod->phone }}</div>
                            </td>
                            <td class="p-3.5">
                                <span class="font-bold text-zinc-800">{{ $mod->moderated_tournaments_count }}</span>
                                <span class="text-xs text-zinc-500">matches</span>
                            </td>
                            <td class="p-3.5">
                                <span class="font-bold text-emerald-600 text-sm">৳ {{ number_format($mod->moderator_balance ?? 0) }}</span>
                            </td>
                            <td class="p-3.5 text-zinc-700 font-medium">
                                ৳ {{ number_format($mod->balance) }}
                            </td>
                            <td class="p-3.5 pr-5 text-right">
                                <form method="POST" action="{{ route('admin.moderators.revoke', $mod) }}" onsubmit="return confirm('Revoke moderator privileges for {{ $mod->name }}?')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold transition-colors">
                                        Revoke Status
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-zinc-400 text-sm">
                                🛡️ No active moderators appointed yet. Use the form above to promote players.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($moderators->hasPages())
            <div class="p-4 border-t border-zinc-100">{{ $moderators->links() }}</div>
        @endif
    </div>
</div>
@endsection
