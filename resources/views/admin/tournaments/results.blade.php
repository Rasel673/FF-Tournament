@extends('admin.layout')
@section('title', 'Match Results & Approval')
@section('heading', 'Results · ' . $tournament->title)

@section('content')
    @php
        $done = $tournament->status === 'completed';
        $pending = $tournament->status === 'pending_approval';
        $isAdmin = (bool) auth()->user()?->is_admin;
        $isModerator = (bool) auth()->user()?->is_moderator && ! $isAdmin;
        $input = 'rounded-lg bg-white border border-zinc-300 text-zinc-900 px-2.5 py-1.5 w-24 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand shadow-sm';
        $readOnly = $done || ($pending && $isModerator);
    @endphp

    {{-- 1. Status Banners --}}
    @if ($done)
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 mb-6 text-sm text-emerald-950 flex items-start gap-3 shadow-sm">
            <span class="text-xl">✅</span>
            <div>
                <p class="font-bold text-emerald-900">Tournament Results Completed & Rewards Disbursed</p>
                <p class="text-emerald-800 text-xs mt-0.5">Player prizes, moderator commission, and platform earnings have been paid out. Below is the final standings record.</p>
            </div>
        </div>
    @elseif ($pending)
        <div class="rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-300 p-5 mb-6 text-sm text-amber-950 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span class="text-2xl">⏳</span>
                    <div>
                        <h3 class="font-bold text-base text-amber-950">
                            @if ($isAdmin)
                                Moderator Submitted Results — Awaiting Your Approval & Payout
                            @else
                                Results Submitted — Awaiting Admin Approval
                            @endif
                        </h3>
                        <p class="text-amber-800 text-xs mt-1">
                            @if ($isAdmin)
                                Match results, positions, and screenshot proof were submitted by <b>{{ $tournament->moderator?->name ?? 'Moderator' }}</b> ({{ $tournament->moderator?->phone }}). Review the standings below. Approving will automatically send prizes to winning players and credit the moderator's earning to their wallet.
                            @else
                                Your submitted match standings and proof screenshot are currently under review by Admin. As soon as Admin approves, winning players will receive their prizes and your commission will be credited to your wallet.
                            @endif
                        </p>
                    </div>
                </div>

                @if ($isAdmin)
                    <div class="flex items-center gap-2 self-start md:self-auto shrink-0">
                        <form method="POST" action="{{ route('admin.tournaments.approve-results', $tournament) }}" onsubmit="return confirm('Approve results and disburse reward money now? Players and Moderator will be credited immediately.')">
                            @csrf
                            <button type="submit" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 text-sm shadow-sm transition-all flex items-center gap-2">
                                <span>⚡</span> Approve & Send Rewards
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Admin Rejection / Revision Note Banner --}}
    @if ($tournament->admin_review_note && ! $done)
        <div class="rounded-2xl bg-rose-50 border border-rose-300 p-4 mb-6 text-sm text-rose-950 flex items-start gap-3 shadow-sm">
            <span class="text-xl">⚠️</span>
            <div>
                <p class="font-bold text-rose-900">Admin Requested Revision</p>
                <p class="text-rose-800 text-xs mt-0.5 font-medium"><b>Admin Feedback:</b> {{ $tournament->admin_review_note }}</p>
                <p class="text-rose-700 text-xs mt-1">Please correct the standings or re-upload the proof screenshot below and submit again for approval.</p>
            </div>
        </div>
    @endif

    {{-- Admin Only: Financial Split Card (70% / 10% / 20%) --}}
    @if ($isAdmin)
        <div class="rounded-2xl bg-gradient-to-r from-orange-50/90 via-amber-50/80 to-emerald-50/90 border border-orange-200/80 p-4 sm:p-5 mb-6 shadow-xs">
            <div class="flex items-center justify-between gap-2 mb-3">
                <div class="flex items-center gap-2 font-bold text-xs uppercase tracking-wider text-orange-950">
                    <span>⚖️</span> Tournament Revenue Split (70% - 10% - 20%)
                </div>
                <span class="text-xs text-orange-900/80 font-medium">Joined: {{ $registrations->count() }} / {{ $tournament->slots }} Players</span>
            </div>
            @php
                $collection = $registrations->count() * (int) $tournament->entry_fee;
                $playerPrize = (int) round($collection * 0.70);
                $modShare = (int) round($collection * 0.10);
                $adminShare = (int) round($collection * 0.20);
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="bg-white/80 rounded-xl p-3 border border-orange-200/50 shadow-2xs">
                    <p class="text-[11px] font-semibold text-zinc-500 uppercase">Total Pool (100%)</p>
                    <p class="text-lg font-black text-zinc-900 mt-0.5">৳{{ number_format($collection) }}</p>
                    <p class="text-[10px] text-zinc-400 mt-0.5">{{ $registrations->count() }} × ৳{{ $tournament->entry_fee }}</p>
                </div>
                <div class="bg-emerald-50/80 rounded-xl p-3 border border-emerald-200/60 shadow-2xs">
                    <p class="text-[11px] font-semibold text-emerald-700 uppercase">🏆 Players (70%)</p>
                    <p class="text-lg font-black text-emerald-900 mt-0.5">৳{{ number_format($playerPrize) }}</p>
                    <p class="text-[10px] text-emerald-600 mt-0.5">Prizes + Kills</p>
                </div>
                <div class="bg-sky-50/80 rounded-xl p-3 border border-sky-200/60 shadow-2xs">
                    <p class="text-[11px] font-semibold text-sky-700 uppercase">🛡️ Moderator (10%)</p>
                    <p class="text-lg font-black text-sky-900 mt-0.5">৳{{ number_format($modShare) }}</p>
                    <p class="text-[10px] text-sky-600 mt-0.5">Mod Earning</p>
                </div>
                <div class="bg-amber-50/80 rounded-xl p-3 border border-amber-200/60 shadow-2xs">
                    <p class="text-[11px] font-semibold text-amber-700 uppercase">👑 Admin (20%)</p>
                    <p class="text-lg font-black text-amber-900 mt-0.5">৳{{ number_format($adminShare) }}</p>
                    <p class="text-[10px] text-amber-600 mt-0.5">Platform Share</p>
                </div>
            </div>
        </div>
    @elseif ($isModerator)
        <div class="rounded-2xl bg-sky-50/80 border border-sky-200/80 p-4 mb-6 shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-lg">🛡️</span>
                <span class="text-sm font-bold text-sky-950">Your Estimated Match Earning:</span>
            </div>
            @php
                $collection = $registrations->count() * (int) $tournament->entry_fee;
                $modShare = (int) round($collection * 0.10);
            @endphp
            <div class="text-right">
                <span class="text-lg font-black text-sky-900">৳{{ number_format($modShare) }} BDT</span>
                <span class="text-xs text-sky-700 block font-medium">Credited upon admin approval</span>
            </div>
        </div>
    @endif

    {{-- Main Form: Results Entry / Update --}}
    <form method="POST" action="{{ route('admin.tournaments.results.store', $tournament) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm overflow-hidden">
            <div class="p-4 bg-zinc-50/70 border-b border-zinc-200 flex items-center justify-between">
                <h3 class="text-sm font-bold text-zinc-800">Player Standings & Kills</h3>
                <span class="text-xs text-zinc-500 font-medium">Per kill bonus: <b class="text-zinc-800">{{ $tournament->per_kill }} BDT</b></span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-zinc-50 text-zinc-600 text-xs uppercase font-semibold border-b border-zinc-200 tracking-wider">
                        <tr>
                            <th class="p-3.5 pl-5">Player</th>
                            <th class="p-3.5">UID</th>
                            <th class="p-3.5">Position</th>
                            <th class="p-3.5">Kills</th>
                            <th class="p-3.5">Position Prize (BDT)</th>
                            <th class="p-3.5 pr-5 text-right">Total Prize</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                    @forelse ($registrations as $reg)
                        @php
                            $r = $resultsByUser->get($reg->user_id);
                            $posVal = old("results.{$reg->user_id}.position", $r?->position);
                            $killVal = old("results.{$reg->user_id}.kills", $r?->kills ?? 0);
                            $prizeVal = old("results.{$reg->user_id}.prize", $r ? max(0, $r->prize - ($r->kills * $tournament->per_kill)) : 0);
                            $totalVal = $r ? $r->prize : ($killVal * $tournament->per_kill + $prizeVal);
                        @endphp
                        <tr class="hover:bg-zinc-50/70 transition-colors">
                            <td class="p-3.5 pl-5 font-semibold text-zinc-900">
                                {{ $reg->user->name }}
                                @if ($reg->in_game_name)
                                    <span class="block text-xs font-normal text-zinc-400">IGN: {{ $reg->in_game_name }}</span>
                                @endif
                            </td>
                            <td class="p-3.5 font-medium text-zinc-600 font-mono text-xs">{{ $reg->in_game_uid ?: $reg->user->uid }}</td>
                            <td class="p-3.5">
                                @if ($readOnly)
                                    <span class="font-bold text-zinc-800">{{ $posVal ? "#{$posVal}" : '-' }}</span>
                                @else
                                    <input type="number" min="1" name="results[{{ $reg->user_id }}][position]" value="{{ $posVal }}" class="{{ $input }}" placeholder="Rank">
                                @endif
                            </td>
                            <td class="p-3.5">
                                @if ($readOnly)
                                    <span class="font-semibold text-zinc-800">{{ $killVal }}</span>
                                @else
                                    <input type="number" min="0" name="results[{{ $reg->user_id }}][kills]" value="{{ $killVal }}" class="{{ $input }}">
                                @endif
                            </td>
                            <td class="p-3.5">
                                @if ($readOnly)
                                    <span class="font-semibold text-zinc-800">{{ $prizeVal }} BDT</span>
                                @else
                                    <input type="number" min="0" name="results[{{ $reg->user_id }}][prize]" value="{{ $prizeVal }}" class="{{ $input }}">
                                @endif
                            </td>
                            <td class="p-3.5 pr-5 text-right font-extrabold text-brand">
                                {{ $totalVal }} BDT
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-zinc-500 font-medium">No players joined yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Note & Proof Upload Section --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-6 space-y-4">
                <h3 class="text-sm font-bold text-zinc-900">Match Note & Result Announcement</h3>
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1.5">Note shown to players in the app</label>
                    <textarea name="result_note" rows="4" @readonly($readOnly) class="w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2 text-zinc-900 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-brand {{ $readOnly ? 'bg-zinc-50 text-zinc-600 cursor-not-allowed' : '' }}">{{ old('result_note', $tournament->result_note) }}</textarea>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-6 space-y-4">
                <h3 class="text-sm font-bold text-zinc-900">Result Screenshot / Scoreboard Proof</h3>
                @if ($tournament->proof_image)
                    <div class="mb-3">
                        <a href="{{ $tournament->proof_image_url }}" target="_blank" class="inline-block group relative">
                            <img src="{{ $tournament->proof_image_url }}" class="h-36 rounded-xl border border-zinc-200 shadow-sm object-cover group-hover:opacity-95 transition-all" alt="Scoreboard Proof">
                            <span class="absolute bottom-2 right-2 bg-black/70 text-white text-[10px] font-semibold px-2 py-0.5 rounded-md">Click to view full</span>
                        </a>
                    </div>
                @else
                    <p class="text-xs text-zinc-400 italic">No scoreboard screenshot uploaded yet.</p>
                @endif

                @unless($readOnly)
                    <div>
                        <label class="block text-xs font-semibold text-zinc-600 mb-1.5">Upload / Replace Proof Screenshot</label>
                        <input type="file" name="proof_image" accept="image/*" class="text-sm text-zinc-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200">
                    </div>
                @endunless
            </div>
        </div>

        {{-- Actions Bar --}}
        <div class="flex flex-wrap items-center justify-between gap-4 pt-2">
            @if ($isModerator && ! $done && ! $pending)
                <button type="submit" class="rounded-xl bg-brand hover:bg-brand-dark text-white font-bold px-6 py-3 shadow-md shadow-orange-500/20 transition-all flex items-center gap-2"
                        onclick="return confirm('Submit match results for Admin approval? Rewards and your commission will be disbursed once approved by admin.')">
                    <span>📤</span> Submit Results for Admin Approval
                </button>
            @elseif ($isModerator && $pending)
                <div class="text-xs font-medium text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5">
                    ⏳ Results have been submitted and are currently awaiting Admin review and approval.
                </div>
            @elseif ($isAdmin)
                <div class="flex flex-wrap items-center gap-3">
                    @if ($pending)
                        <button type="submit" class="rounded-xl bg-zinc-800 hover:bg-zinc-900 text-white font-semibold px-5 py-2.5 text-sm shadow-sm transition-all">
                            Save Changes to Standings
                        </button>
                    @elseif (! $done)
                        <button type="submit" class="rounded-xl bg-brand hover:bg-brand-dark text-white font-bold px-6 py-2.5 shadow-md shadow-orange-500/20 transition-all"
                                onclick="return confirm('Publish results and disburse prizes immediately?')">
                            Publish Results & Disburse Rewards
                        </button>
                    @else
                        <button type="submit" class="rounded-xl bg-zinc-800 hover:bg-zinc-900 text-white font-semibold px-5 py-2.5 text-sm shadow-sm transition-all">
                            Update Note & Proof
                        </button>
                    @endif
                </div>
            @endif

            <a href="{{ route('admin.tournaments.index') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800 underline">
                ← Back to Tournaments
            </a>
        </div>
    </form>

    {{-- Admin Rejection Form Modal / Section --}}
    @if ($isAdmin && $pending)
        <div class="mt-8 pt-6 border-t border-zinc-200">
            <div class="rounded-2xl bg-rose-50/50 border border-rose-200 p-6 max-w-xl">
                <h4 class="text-sm font-bold text-rose-900 flex items-center gap-2 mb-2">
                    <span>↩️</span> Reject & Request Revision from Moderator
                </h4>
                <p class="text-xs text-rose-800 mb-4">
                    If the submitted proof screenshot is invalid, standings are wrong, or UID does not match, return it to the moderator with a note explaining what needs to be fixed.
                </p>

                <form method="POST" action="{{ route('admin.tournaments.reject-results', $tournament) }}" class="space-y-3" onsubmit="return confirm('Reject results and request revision from moderator?')">
                    @csrf
                    <textarea name="admin_review_note" rows="2" required placeholder="Explain why the results are rejected (e.g. Winner position #1 UID does not match the proof screenshot)..." class="w-full rounded-xl bg-white border border-rose-300 px-3.5 py-2 text-zinc-900 text-sm shadow-xs focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>

                    <button type="submit" class="rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold px-4 py-2 text-xs shadow-xs transition-all">
                        Reject Results & Return to Moderator
                    </button>
                </form>
            </div>
        </div>
    @endif
@endsection
