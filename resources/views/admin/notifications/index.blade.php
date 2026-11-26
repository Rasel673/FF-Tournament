@extends('admin.layout')
@section('title', 'Push Notifications')
@section('heading', 'Push Notifications')

@section('content')
<div class="space-y-6">
    <!-- Status / Info Bar -->
    <div class="rounded-2xl bg-gradient-to-r from-orange-500/10 via-amber-500/10 to-transparent border border-orange-200/80 p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3.5">
            <div class="h-11 w-11 rounded-xl bg-gradient-to-tr from-brand to-amber-500 flex items-center justify-center text-white shadow-md shadow-orange-500/20 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="font-bold text-zinc-900 text-base">OneSignal Push Service</h2>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Connected
                    </span>
                </div>
                <p class="text-xs text-zinc-600 mt-0.5">Send instant push notifications to all tournament players or a specific user's mobile device.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs">
            <a href="{{ route('admin.settings.edit') }}" class="px-3 py-1.5 rounded-xl bg-white border border-zinc-200 text-zinc-700 font-semibold hover:border-zinc-300 hover:bg-zinc-50 transition-colors shadow-sm inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                Configure Keys
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Compose Form (5 Cols on LG) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-5 sm:p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-zinc-100">
                    <div>
                        <h3 class="font-bold text-zinc-900 text-base">Compose Notification</h3>
                        <p class="text-xs text-zinc-500">Create a new push notification broadcast</p>
                    </div>
                    <span class="text-xl">🚀</span>
                </div>

                <form method="POST" action="{{ route('admin.notifications.store') }}" enctype="multipart/form-data" class="space-y-4" id="notificationForm">
                    @csrf

                    <!-- Target Selection -->
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-2">Target Audience</label>
                        <div class="grid grid-cols-2 gap-2 p-1 bg-zinc-100 rounded-xl border border-zinc-200">
                            <label class="cursor-pointer">
                                <input type="radio" name="target" value="all" id="targetAll" class="peer sr-only" {{ old('target', $preselectedUserId ? 'specific' : 'all') === 'all' ? 'checked' : '' }}>
                                <div class="py-2 px-3 text-center rounded-lg text-xs font-bold transition-all peer-checked:bg-white peer-checked:text-brand peer-checked:shadow-sm text-zinc-600 hover:text-zinc-900 flex items-center justify-center gap-1.5">
                                    <span>📢</span>
                                    <span>All Users</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="target" value="specific" id="targetSpecific" class="peer sr-only" {{ old('target', $preselectedUserId ? 'specific' : 'all') === 'specific' ? 'checked' : '' }}>
                                <div class="py-2 px-3 text-center rounded-lg text-xs font-bold transition-all peer-checked:bg-white peer-checked:text-brand peer-checked:shadow-sm text-zinc-600 hover:text-zinc-900 flex items-center justify-center gap-1.5">
                                    <span>👤</span>
                                    <span>Specific User</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- User Selector (shown only if specific user) -->
                    <div id="userSelectWrapper" class="{{ old('target', $preselectedUserId ? 'specific' : 'all') === 'specific' ? '' : 'hidden' }} space-y-1.5 bg-orange-50/60 p-3.5 rounded-xl border border-orange-200/80">
                        <label class="block text-xs font-semibold text-orange-950">Select Player <span class="text-rose-500">*</span></label>
                        <select name="user_id" id="userSelect" class="w-full rounded-xl bg-white border border-orange-200 px-3 py-2 text-xs font-medium text-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand shadow-sm">
                            <option value="">-- Choose a Player --</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" {{ (string) old('user_id', $preselectedUserId) === (string) $u->id ? 'selected' : '' }}>
                                    #{{ $u->id }} - {{ $u->name }} (UID: {{ $u->uid ?? 'N/A' }} | {{ $u->phone }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-orange-800">Targeted push will be delivered to this player's OneSignal device (external_id: Player ID).</p>
                    </div>

                    <!-- Heading / Title -->
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" id="inputTitle" value="{{ old('title') }}" required placeholder="e.g. ম্যাচ শুরু হচ্ছে or Important Announcement"
                               class="w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    </div>

                    <!-- Message Body -->
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Message Body <span class="text-rose-500">*</span></label>
                        <textarea name="message" id="inputMessage" rows="3" required placeholder="e.g. BR Rank Push ১০ মিনিটের মধ্যে শুরু হবে। Room ID দেখে নিন।"
                                  class="w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand">{{ old('message') }}</textarea>
                    </div>

                    <!-- Big Picture Banner -->
                    <div class="space-y-2 pt-2 border-t border-zinc-100">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-zinc-700">Big Picture Banner (Optional)</label>
                            <span class="text-[11px] text-zinc-400">Large preview image</span>
                        </div>
                        <input type="file" name="big_picture_file" id="inputBigPictureFile" accept="image/*"
                               class="block w-full text-xs text-zinc-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-orange-50 file:text-brand hover:file:bg-orange-100 cursor-pointer">
                        <div class="relative">
                            <input type="url" name="big_picture_url" id="inputBigPictureUrl" value="{{ old('big_picture_url') }}" placeholder="Or paste image URL (https://...)"
                                   class="w-full rounded-xl bg-white border border-zinc-300 px-3 py-1.5 text-xs text-zinc-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        </div>
                    </div>

                    <!-- Large Icon (Avatar) -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-zinc-700">Large Icon / Avatar (Optional)</label>
                            <span class="text-[11px] text-zinc-400">Square notification icon</span>
                        </div>
                        <input type="file" name="large_icon_file" id="inputLargeIconFile" accept="image/*"
                               class="block w-full text-xs text-zinc-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-orange-50 file:text-brand hover:file:bg-orange-100 cursor-pointer">
                        <input type="url" name="large_icon_url" id="inputLargeIconUrl" value="{{ old('large_icon_url') }}" placeholder="Or paste icon URL (https://...)"
                               class="w-full rounded-xl bg-white border border-zinc-300 px-3 py-1.5 text-xs text-zinc-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    </div>

                    <!-- Deep Link Action -->
                    <div class="pt-2 border-t border-zinc-100 space-y-2">
                        <label class="block text-xs font-semibold text-zinc-700">Tap Action / Deep Link</label>
                        <select name="data_type" id="selectDataType" class="w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2 text-xs font-medium text-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand shadow-sm">
                            <option value="none">Default (Just open App)</option>
                            <option value="tournament" {{ old('data_type') === 'tournament' ? 'selected' : '' }}>Open Specific Tournament Match</option>
                            <option value="wallet" {{ old('data_type') === 'wallet' ? 'selected' : '' }}>Open Wallet Screen</option>
                            <option value="profile" {{ old('data_type') === 'profile' ? 'selected' : '' }}>Open Profile Screen</option>
                        </select>

                        <!-- Tournament Match Dropdown -->
                        <div id="tournamentSelectWrapper" class="{{ old('data_type') === 'tournament' ? '' : 'hidden' }} pt-1">
                            <select name="tournament_id" id="tournamentSelect" class="w-full rounded-xl bg-orange-50/60 border border-orange-200 px-3 py-2 text-xs font-semibold text-zinc-800 focus:outline-none focus:ring-2 focus:ring-brand shadow-sm">
                                <option value="">-- Choose Tournament --</option>
                                @foreach ($tournaments as $t)
                                    <option value="{{ $t->id }}" {{ (string) old('tournament_id') === (string) $t->id ? 'selected' : '' }}>
                                        #{{ $t->id }} {{ $t->title }} ({{ $t->match_number ?? 'Match' }}) - {{ ucfirst($t->status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Send Button -->
                    <div class="pt-3">
                        <button type="submit" id="submitBtn" class="w-full rounded-xl bg-gradient-to-r from-brand via-orange-600 to-amber-500 hover:from-brand-dark hover:to-orange-600 text-white font-bold py-3 shadow-md shadow-orange-500/25 transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <span>Send Push Notification Now</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Live Android Notification Preview -->
            <div class="rounded-2xl bg-zinc-900 border border-zinc-800 p-5 shadow-lg text-white space-y-3">
                <div class="flex items-center justify-between text-xs text-zinc-400 border-b border-zinc-800 pb-2">
                    <span class="flex items-center gap-1.5 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Live Phone Push Preview
                    </span>
                    <span>Android System</span>
                </div>

                <!-- Notification Card Simulation -->
                <div class="bg-zinc-800/90 rounded-xl p-3.5 border border-zinc-700/60 space-y-2 shadow-sm">
                    <div class="flex items-center justify-between text-[11px] text-zinc-400">
                        <div class="flex items-center gap-1.5">
                            <span class="p-1 rounded bg-orange-600 text-[10px]">🔥</span>
                            <span class="font-bold text-zinc-300">FF Tournament</span>
                            <span>•</span>
                            <span>now</span>
                        </div>
                        <span class="text-[10px] text-zinc-500">push</span>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-sm text-white truncate" id="previewTitle">ম্যাচ শুরু হচ্ছে</div>
                            <div class="text-xs text-zinc-300 leading-snug line-clamp-2 mt-0.5" id="previewMessage">BR Rank Push ১০ মিনিটের মধ্যে শুরু হবে। Room ID দেখে নিন।</div>
                        </div>
                        <div id="previewIconWrapper" class="shrink-0 h-10 w-10 rounded-lg bg-zinc-700 overflow-hidden flex items-center justify-center text-xs">
                            <img id="previewIconImg" src="https://via.placeholder.com/60?text=FF" class="h-full w-full object-cover hidden" alt="icon">
                            <span id="previewIconPlaceholder">🎮</span>
                        </div>
                    </div>

                    <div id="previewBannerWrapper" class="rounded-lg overflow-hidden border border-zinc-700 mt-2 hidden max-h-36">
                        <img id="previewBannerImg" src="" class="w-full object-cover" alt="banner">
                    </div>
                </div>
            </div>
        </div>

        <!-- History & Logs (7 Cols on LG) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-zinc-900 text-base">Sent Notifications History</h3>
                        <p class="text-xs text-zinc-500">Record of all broadcasts and targeted push notifications</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-zinc-100 text-zinc-700 border border-zinc-200">
                        Total: {{ $notifications->total() }}
                    </span>
                </div>

                <div class="divide-y divide-zinc-100">
                    @forelse ($notifications as $notification)
                        <div class="p-4 sm:p-5 hover:bg-zinc-50/70 transition-colors flex flex-col sm:flex-row items-start justify-between gap-4">
                            <div class="flex items-start gap-3.5 flex-1 min-w-0">
                                @if ($notification->small_icon_url)
                                    <img src="{{ $notification->small_icon_url }}" class="h-10 w-10 rounded-xl object-cover shrink-0 border border-zinc-200 shadow-sm" alt="icon">
                                @else
                                    <div class="h-10 w-10 rounded-xl bg-orange-100 text-brand flex items-center justify-center shrink-0 text-base font-bold">
                                        🔔
                                    </div>
                                @endif

                                <div class="space-y-1 min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-zinc-900 text-sm">{{ $notification->title }}</span>
                                        @if ($notification->target === 'all')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200 inline-flex items-center gap-1">
                                                <span>📢</span> All Users
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 inline-flex items-center gap-1">
                                                <span>👤</span>
                                                @if ($notification->user)
                                                    <a href="{{ route('admin.users.show', $notification->user) }}" class="hover:underline font-semibold">
                                                        {{ $notification->user->name }} (#{{ $notification->user->id }})
                                                    </a>
                                                @else
                                                    User #{{ $notification->external_id ?? $notification->user_id }}
                                                @endif
                                            </span>
                                        @endif

                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $notification->status === 'sent' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-200' }}">
                                            {{ ucfirst($notification->status) }}
                                        </span>
                                    </div>

                                    <p class="text-xs text-zinc-600 line-clamp-2">{{ $notification->description }}</p>

                                    @if ($notification->large_image_url)
                                        <div class="pt-1.5">
                                            <a href="{{ $notification->large_image_url }}" target="_blank" class="inline-block group">
                                                <img src="{{ $notification->large_image_url }}" class="h-16 w-32 object-cover rounded-lg border border-zinc-200 group-hover:opacity-90 shadow-xs" alt="banner">
                                            </a>
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-3 pt-1 text-[11px] text-zinc-400 flex-wrap">
                                        <span>🕒 {{ $notification->created_at->format('d M Y, h:i A') }}</span>
                                        @if ($notification->onesignal_id)
                                            <span class="font-mono text-zinc-500">ID: {{ Str::limit($notification->onesignal_id, 12) }}</span>
                                        @endif
                                        @if ($notification->data)
                                            <span class="bg-zinc-100 px-1.5 py-0.5 rounded text-zinc-600 font-mono text-[10px]">
                                                Payload: {{ json_encode($notification->data) }}
                                            </span>
                                        @endif
                                        @if ($notification->error_message)
                                            <span class="text-rose-600 font-semibold">Error: {{ Str::limit($notification->error_message, 40) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="shrink-0 flex items-center gap-2 self-end sm:self-start">
                                <form method="POST" action="{{ route('admin.notifications.destroy', $notification) }}" onsubmit="return confirm('Remove this notification from log?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-zinc-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Delete record">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-zinc-400">
                            <span class="text-3xl block mb-2">📭</span>
                            <div class="font-semibold text-zinc-600 text-sm">No notifications sent yet</div>
                            <p class="text-xs text-zinc-400 mt-1">Compose your first push notification above to engage players!</p>
                        </div>
                    @endforelse
                </div>

                @if ($notifications->hasPages())
                    <div class="p-4 border-t border-zinc-100">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const targetAll = document.getElementById('targetAll');
        const targetSpecific = document.getElementById('targetSpecific');
        const userSelectWrapper = document.getElementById('userSelectWrapper');
        const userSelect = document.getElementById('userSelect');

        const selectDataType = document.getElementById('selectDataType');
        const tournamentSelectWrapper = document.getElementById('tournamentSelectWrapper');

        const inputTitle = document.getElementById('inputTitle');
        const inputMessage = document.getElementById('inputMessage');
        const previewTitle = document.getElementById('previewTitle');
        const previewMessage = document.getElementById('previewMessage');

        const inputBigPictureUrl = document.getElementById('inputBigPictureUrl');
        const inputBigPictureFile = document.getElementById('inputBigPictureFile');
        const previewBannerWrapper = document.getElementById('previewBannerWrapper');
        const previewBannerImg = document.getElementById('previewBannerImg');

        const inputLargeIconUrl = document.getElementById('inputLargeIconUrl');
        const inputLargeIconFile = document.getElementById('inputLargeIconFile');
        const previewIconImg = document.getElementById('previewIconImg');
        const previewIconPlaceholder = document.getElementById('previewIconPlaceholder');

        // Toggle User Select
        function toggleTarget() {
            if (targetSpecific.checked) {
                userSelectWrapper.classList.remove('hidden');
                userSelect.setAttribute('required', 'required');
            } else {
                userSelectWrapper.classList.add('hidden');
                userSelect.removeAttribute('required');
            }
        }
        targetAll.addEventListener('change', toggleTarget);
        targetSpecific.addEventListener('change', toggleTarget);

        // Toggle Deep Link Tournament Select
        selectDataType.addEventListener('change', function () {
            if (this.value === 'tournament') {
                tournamentSelectWrapper.classList.remove('hidden');
            } else {
                tournamentSelectWrapper.classList.add('hidden');
            }
        });

        // Live text preview
        inputTitle.addEventListener('input', function () {
            previewTitle.textContent = this.value.trim() || 'ম্যাচ শুরু হচ্ছে';
        });

        inputMessage.addEventListener('input', function () {
            previewMessage.textContent = this.value.trim() || 'BR Rank Push ১০ মিনিটের মধ্যে শুরু হবে। Room ID দেখে নিন।';
        });

        // Live banner preview
        inputBigPictureUrl.addEventListener('input', function () {
            if (this.value.trim()) {
                previewBannerImg.src = this.value.trim();
                previewBannerWrapper.classList.remove('hidden');
            } else if (!inputBigPictureFile.files.length) {
                previewBannerWrapper.classList.add('hidden');
            }
        });

        inputBigPictureFile.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewBannerImg.src = e.target.result;
                    previewBannerWrapper.classList.remove('hidden');
                };
                reader.readAsDataURL(this.files[0]);
            } else if (!inputBigPictureUrl.value.trim()) {
                previewBannerWrapper.classList.add('hidden');
            }
        });

        // Live icon preview
        inputLargeIconUrl.addEventListener('input', function () {
            if (this.value.trim()) {
                previewIconImg.src = this.value.trim();
                previewIconImg.classList.remove('hidden');
                previewIconPlaceholder.classList.add('hidden');
            } else if (!inputLargeIconFile.files.length) {
                previewIconImg.classList.add('hidden');
                previewIconPlaceholder.classList.remove('hidden');
            }
        });

        inputLargeIconFile.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewIconImg.src = e.target.result;
                    previewIconImg.classList.remove('hidden');
                    previewIconPlaceholder.classList.add('hidden');
                };
                reader.readAsDataURL(this.files[0]);
            } else if (!inputLargeIconUrl.value.trim()) {
                previewIconImg.classList.add('hidden');
                previewIconPlaceholder.classList.remove('hidden');
            }
        });
    });
</script>
@endsection

