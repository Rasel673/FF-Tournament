@extends('admin.layout')
@section('title', 'Settings')
@section('heading', 'Settings')

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-xl rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-6 sm:p-8 space-y-5">
        @csrf
        @foreach ($fields as $key => $label)
            <div>
                <label class="block text-sm font-semibold text-zinc-700 mb-1.5">{{ $label }}</label>
                <input name="{{ $key }}" value="{{ old($key, $values[$key]) }}"
                       @if (str_starts_with($key, 'min_')) type="number" min="1" required @endif
                       class="w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2.5 text-zinc-900 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-brand placeholder-zinc-400">
            </div>
        @endforeach
        <button class="rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold px-6 py-2.5 shadow-sm shadow-orange-500/20 transition-all">Save Settings</button>
    </form>

    {{-- Change Password --}}
    <div class="max-w-xl mt-8">
        <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-slate-700 to-slate-500 flex items-center justify-center text-white shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Change Password</h2>
                    <p class="text-xs text-slate-500">Update your admin account password</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.password.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Current Password</label>
                    <input type="password" name="current_password" required autocomplete="current-password"
                           class="w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2.5 text-zinc-900 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-brand placeholder-zinc-400"
                           placeholder="Enter current password">
                    @error('current_password')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">New Password</label>
                    <input type="password" name="password" required autocomplete="new-password"
                           class="w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2.5 text-zinc-900 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-brand placeholder-zinc-400"
                           placeholder="Minimum 8 characters">
                    @error('password')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2.5 text-zinc-900 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-brand placeholder-zinc-400"
                           placeholder="Re-enter new password">
                </div>

                <button class="rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold px-6 py-2.5 shadow-sm transition-all">
                    Update Password
                </button>
            </form>
        </div>
    </div>
@endsection
