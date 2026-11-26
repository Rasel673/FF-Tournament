@extends('admin.layout')
@php $editing = $tournament->exists; @endphp
@section('title', $editing ? 'Edit tournament' : 'New tournament')
@section('heading', $editing ? 'Edit tournament' : 'New tournament')

@section('content')
    @php
        $input = 'w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2.5 text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand shadow-sm text-sm transition-all';
        $prizes = $tournament->prize_pool_list;
    @endphp

    @if ($errors->any())
        <div class="max-w-3xl mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-medium text-rose-800">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $editing ? route('admin.tournaments.update', $tournament) : route('admin.tournaments.store') }}"
          class="max-w-3xl rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-6 sm:p-8 space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <!-- General Info Section -->
        <div>
            <h3 class="text-sm font-bold text-zinc-900 border-b border-zinc-100 pb-2 mb-4">Match Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Match Title</label>
                    <input name="title" value="{{ old('title', $tournament->title) }}" required class="{{ $input }}" placeholder="e.g. BR SOLO MATCH or PRO LEAGUE">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Match No. <span class="text-xs text-zinc-400 font-normal">(Leave empty to auto-generate)</span></label>
                    <input name="match_number" value="{{ old('match_number', $tournament->match_number) }}" class="{{ $input }}" placeholder="e.g. No.206513">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Game Category / Mode</label>
                    <select name="category_id" class="{{ $input }}">
                        <option value="">-- Select Mode (Optional) --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $tournament->category_id) == $cat->id)>
                                {{ $cat->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Match Type</label>
                    <select name="type" class="{{ $input }}">
                        @foreach (['Solo', 'Duo', 'Squad', '4v4'] as $t)
                            <option value="{{ $t }}" @selected(old('type', $tournament->type ?? 'Solo') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Map</label>
                    <input name="map" value="{{ old('map', $tournament->map ?? 'Bermuda') }}" required class="{{ $input }}" placeholder="Bermuda">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Status</label>
                    <select name="status" class="{{ $input }}">
                        @foreach (['upcoming', 'active', 'completed'] as $s)
                            <option value="{{ $s }}" @selected(old('status', $tournament->status) === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Financial & Slots Section -->
        <div>
            <h3 class="text-sm font-bold text-zinc-900 border-b border-zinc-100 pb-2 mb-4">Fees & Slots</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Entry fee (BDT)</label>
                    <input type="number" min="0" name="entry_fee" id="entryFeeInput" value="{{ old('entry_fee', $tournament->entry_fee ?? 10) }}" required class="{{ $input }}">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Total Prize (BDT)</label>
                    <input type="number" min="0" name="prize" id="totalPrizeInput" value="{{ old('prize', $tournament->prize ?? 336) }}" required class="{{ $input }}">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Per kill (BDT)</label>
                    <input type="number" min="0" name="per_kill" value="{{ old('per_kill', $tournament->per_kill ?? 0) }}" required class="{{ $input }}">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Total slots</label>
                    <input type="number" min="1" name="slots" id="slotsInput" value="{{ old('slots', $tournament->slots ?? 48) }}" required class="{{ $input }}">
                </div>
            </div>

            @if (auth()->user()?->is_admin)
                <!-- 70% / 10% / 20% Revenue Policy Card (Admin Only) -->
                <div class="mt-4 rounded-2xl bg-gradient-to-r from-orange-50/90 via-amber-50/80 to-emerald-50/90 border border-orange-200/80 p-4 sm:p-5 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">⚖️</span>
                            <div>
                                <span class="text-xs font-bold text-slate-900 uppercase tracking-wider">Tournament Entry Revenue Split (70% - 10% - 20%)</span>
                                <div class="text-[11px] text-slate-500">70% Reward to Players • 10% Moderator Earning • 20% Admin Income</div>
                            </div>
                        </div>
                        <button type="button" id="applySplitBtn" class="px-3.5 py-1.5 text-xs font-bold bg-gradient-to-r from-brand via-orange-600 to-amber-500 hover:opacity-95 text-white rounded-xl shadow-xs transition-all inline-flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
                            <span>⚡ Apply 70/10/20 Formula</span>
                        </button>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-center text-xs">
                        <div class="bg-white/95 border border-slate-200/80 rounded-xl p-3 shadow-xs">
                            <div class="text-[10px] text-slate-500 font-semibold uppercase">Total Pool (100%)</div>
                            <div id="splitTotalPool" class="font-extrabold text-slate-900 text-sm mt-1">৳0</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">Slots × Entry</div>
                        </div>
                        <div class="bg-emerald-50/95 border border-emerald-200/80 rounded-xl p-3 shadow-xs">
                            <div class="text-[10px] text-emerald-700 font-semibold uppercase">🏆 Player Prize (70%)</div>
                            <div id="splitPrizePool" class="font-extrabold text-emerald-800 text-sm mt-1">৳0</div>
                            <div class="text-[10px] text-emerald-600 mt-0.5">Total Rewards</div>
                        </div>
                        <div class="bg-blue-50/95 border border-blue-200/80 rounded-xl p-3 shadow-xs">
                            <div class="text-[10px] text-blue-700 font-semibold uppercase">🛡️ Moderator (10%)</div>
                            <div id="splitModCommission" class="font-extrabold text-blue-800 text-sm mt-1">৳0</div>
                            <div class="text-[10px] text-blue-600 mt-0.5">Mod Earning</div>
                        </div>
                        <div class="bg-amber-50/95 border border-amber-200/80 rounded-xl p-3 shadow-xs">
                            <div class="text-[10px] text-amber-700 font-semibold uppercase">👑 Admin Income (20%)</div>
                            <div id="splitAdminIncome" class="font-extrabold text-amber-800 text-sm mt-1">৳0</div>
                            <div class="text-[10px] text-amber-600 mt-0.5">Platform Revenue</div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Moderator View: Shows only his own earning without revealing admin income or revenue split -->
                <div class="mt-4 rounded-2xl bg-gradient-to-r from-blue-50/90 via-indigo-50/80 to-sky-50/90 border border-blue-200/80 p-4 shadow-xs flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-lg">
                            💰
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 uppercase tracking-wider">Your Estimated Match Earning</div>
                            <div class="text-[11px] text-slate-500">Credited to your moderator balance upon match completion</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div id="modOnlyEarning" class="text-xl font-black text-blue-700">৳{{ $tournament->moderator_commission ?? 0 }}</div>
                        <div class="text-[10px] text-slate-500 font-medium">Your Earning</div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Schedule & Room Info Section -->
        <div>
            <h3 class="text-sm font-bold text-zinc-900 border-b border-zinc-100 pb-2 mb-4">Schedule & Room Access</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Match start time</label>
                    <input type="datetime-local" name="start_time" value="{{ old('start_time', $tournament->start_time?->format('Y-m-d\TH:i')) }}" required class="{{ $input }}">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Registration opens at <span class="text-zinc-400 font-normal">(Optional)</span></label>
                    <input type="datetime-local" name="open_time" value="{{ old('open_time', $tournament->open_time?->format('Y-m-d\TH:i')) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Room ID</label>
                    <input name="room_id" value="{{ old('room_id', $tournament->room_id) }}" class="{{ $input }}" placeholder="Secret Room ID">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Room password</label>
                    <input name="room_password" value="{{ old('room_password', $tournament->room_password) }}" class="{{ $input }}" placeholder="Secret Room Password">
                </div>
            </div>
        </div>

        <!-- Moderator & Commission Section -->
        <div>
            <h3 class="text-sm font-bold text-zinc-900 border-b border-zinc-100 pb-2 mb-4">Assigned Moderator & Commission</h3>
            @if (auth()->user()?->is_admin)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Assign Moderator</label>
                        <select name="moderator_id" class="{{ $input }}">
                            <option value="">-- No Moderator (Admin Managed) --</option>
                            @if (isset($moderators))
                                @foreach ($moderators as $mod)
                                    <option value="{{ $mod->id }}" @selected(old('moderator_id', $tournament->moderator_id) == $mod->id)>
                                        {{ $mod->name }} ({{ $mod->phone }} | UID: {{ $mod->uid }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <p class="text-[11px] text-zinc-500 mt-1">Assigned moderator can enter Room ID, set results, and earn match commission.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Moderator Commission (10% Earning)</label>
                        <input type="number" min="0" name="moderator_commission" id="moderatorCommissionInput" value="{{ old('moderator_commission', $tournament->moderator_commission ?? 50) }}" class="{{ $input }}" placeholder="e.g. 50">
                        <p class="text-[11px] text-zinc-500 mt-1">Paid to the moderator automatically upon match completion (10% of entry fees).</p>
                    </div>
                </div>
            @else
                <input type="hidden" name="moderator_id" value="{{ auth()->id() }}">
                <div class="rounded-xl bg-blue-50/80 border border-blue-200/90 p-4 text-xs text-blue-900 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="text-lg">🛡️</span>
                        <div>
                            <div class="font-bold">You are the assigned moderator for this tournament.</div>
                            <div class="text-blue-700 mt-0.5">Enter Room ID & Password above when ready. Match commission will be credited upon publishing results.</div>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full font-bold bg-blue-100 text-blue-800 border border-blue-200">
                        Commission: ৳{{ $tournament->moderator_commission ?? auth()->user()->moderator_commission_rate ?? 50 }}
                    </span>
                </div>
            @endif
        </div>

        <!-- Prize Pool Breakdown Section (As in App UI Image 4) -->
        <div class="rounded-xl bg-orange-50/40 border border-orange-200/70 p-4 sm:p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 flex items-center gap-1.5">
                        <span>🏆</span> Tournament Prize Pool Breakdown
                    </h3>
                    <p class="text-xs text-zinc-500 mt-0.5">Shown in the app's "VIEW MORE" prize pool bottom sheet.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">👑 Winner (1st)</label>
                    <input type="hidden" name="prize_positions[]" value="Winner">
                    <input type="number" min="0" name="prize_amounts[]" value="{{ old('prize_amounts.0', $prizes[0]['prize'] ?? 160) }}" placeholder="160" class="{{ $input }} text-xs py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">🥈 2nd Position</label>
                    <input type="hidden" name="prize_positions[]" value="2nd Position">
                    <input type="number" min="0" name="prize_amounts[]" value="{{ old('prize_amounts.1', $prizes[1]['prize'] ?? 110) }}" placeholder="110" class="{{ $input }} text-xs py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">🥉 3rd Position</label>
                    <input type="hidden" name="prize_positions[]" value="3rd Position">
                    <input type="number" min="0" name="prize_amounts[]" value="{{ old('prize_amounts.2', $prizes[2]['prize'] ?? 80) }}" placeholder="80" class="{{ $input }} text-xs py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">🏅 4th Position</label>
                    <input type="hidden" name="prize_positions[]" value="4th Position">
                    <input type="number" min="0" name="prize_amounts[]" value="{{ old('prize_amounts.3', $prizes[3]['prize'] ?? 40) }}" placeholder="40" class="{{ $input }} text-xs py-2">
                </div>
            </div>
        </div>

        <!-- Rules & Video Tutorial Section (As in App UI Image 5) -->
        <div>
            <h3 class="text-sm font-bold text-zinc-900 border-b border-zinc-100 pb-2 mb-4">Rules & Regulations (App View)</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Tutorial Video URL (Optional)</label>
                    <input type="url" name="tutorial_video_url" value="{{ old('tutorial_video_url', $tournament->tutorial_video_url) }}" class="{{ $input }}" placeholder="https://www.youtube.com/watch?v=...">
                    <p class="text-[11px] text-zinc-400 mt-1">App displays a "WATCH TUTORIAL VIDEO" button on the Join screen.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Rules Section Title</label>
                    <input name="rules_title" value="{{ old('rules_title', $tournament->rules_title ?? 'SQUAD SCRIM MATCH RULES') }}" class="{{ $input }}" placeholder="SQUAD SCRIM MATCH RULES">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Match Rules (1 rule per line)</label>
                    @php
                        $defaultRulesText = implode("\n", [
                            'ম্যাচে জয়েন করার সময় আপনার গেম আইডি ভালোভাবে মিলিয়ে নিন। গেম আইডির পরিবর্তে অন্য কিছু লিখলে ম্যাচ খেলতে দেওয়া হবে না।',
                            'স্কোয়াডের সব সদস্যের গেম আইডির লেভেল কমপক্ষে ৪০ হতে হবে। লেভেল ৪০-এর নিচে হলে ম্যাচ খেলতে দেওয়া হবে না।',
                            'Custom Room-এ জয়েন করার সময় স্কোয়াডের সবাইকে একসঙ্গে গ্রুপে নিয়ে জয়েন করতে হবে। আলাদা করে জয়েন করার কারণে অন্য দলের প্লেয়ার আপনার দলে ঢুকে গেলে কর্তৃপক্ষ দায়ী থাকবে না।'
                        ]);
                    @endphp
                    <textarea name="rules" rows="4" class="{{ $input }} font-sans">{{ old('rules', $tournament->rules ?: $defaultRulesText) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-3 border-t border-zinc-100">
            <button class="rounded-xl bg-gradient-to-r from-brand via-orange-600 to-amber-500 hover:opacity-95 text-white font-semibold px-6 py-2.5 shadow-md shadow-orange-500/20 transition-all">
                Save Tournament
            </button>
            <a href="{{ route('admin.tournaments.index') }}" class="rounded-xl bg-white border border-zinc-300 text-zinc-700 hover:bg-zinc-50 font-semibold px-6 py-2.5 shadow-sm transition-all">Cancel</a>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const entryFeeInput = document.getElementById('entryFeeInput');
            const slotsInput = document.getElementById('slotsInput');
            const totalPrizeInput = document.getElementById('totalPrizeInput');
            const moderatorCommissionInput = document.getElementById('moderatorCommissionInput');
            const applySplitBtn = document.getElementById('applySplitBtn');

            function calculateSplit() {
                const fee = Math.max(0, parseInt(entryFeeInput ? entryFeeInput.value : 0) || 0);
                const slots = Math.max(0, parseInt(slotsInput ? slotsInput.value : 0) || 0);
                const total = fee * slots;

                const prize70 = Math.round(total * 0.70);
                const mod10 = Math.round(total * 0.10);
                const admin20 = Math.round(total * 0.20);

                const elTotal = document.getElementById('splitTotalPool');
                const elPrize = document.getElementById('splitPrizePool');
                const elMod = document.getElementById('splitModCommission');
                const elAdmin = document.getElementById('splitAdminIncome');

                const elModOnly = document.getElementById('modOnlyEarning');
                if (elModOnly) elModOnly.textContent = '৳' + mod10.toLocaleString();

                if (elTotal) elTotal.textContent = '৳' + total.toLocaleString();
                if (elPrize) elPrize.textContent = '৳' + prize70.toLocaleString();
                if (elMod) elMod.textContent = '৳' + mod10.toLocaleString();
                if (elAdmin) elAdmin.textContent = '৳' + admin20.toLocaleString();

                return { total, prize70, mod10, admin20 };
            }

            if (entryFeeInput) entryFeeInput.addEventListener('input', calculateSplit);
            if (slotsInput) slotsInput.addEventListener('input', calculateSplit);
            calculateSplit();

            if (applySplitBtn) {
                applySplitBtn.addEventListener('click', function () {
                    const split = calculateSplit();
                    if (totalPrizeInput) {
                        totalPrizeInput.value = split.prize70;
                    }
                    if (moderatorCommissionInput) {
                        moderatorCommissionInput.value = split.mod10;
                    }

                    // Distribute prize breakdown positions proportionally (41%, 28%, 20%, 11%)
                    const posInputs = document.querySelectorAll('input[name="prize_amounts[]"]');
                    if (posInputs && posInputs.length >= 4) {
                        const p1 = Math.round(split.prize70 * 0.41);
                        const p2 = Math.round(split.prize70 * 0.28);
                        const p3 = Math.round(split.prize70 * 0.20);
                        const p4 = Math.max(0, split.prize70 - p1 - p2 - p3);
                        posInputs[0].value = p1;
                        posInputs[1].value = p2;
                        posInputs[2].value = p3;
                        posInputs[3].value = p4;
                    }
                });
            }
        });
    </script>
@endsection
