<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · FF Tournament</title>
    {{-- Tailwind via CDN keeps setup zero-build. For production, swap with Vite + Tailwind (already in a fresh Laravel). --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: { DEFAULT: '#E0491A', dark: '#B93A12' } } } } }
    </script>
</head>
<body class="bg-zinc-950 text-zinc-200 min-h-screen">
@php
    $nav = [
        ['admin.dashboard', 'Dashboard', route('admin.dashboard')],
        ['admin.tournaments.*', 'Tournaments', route('admin.tournaments.index')],
        ['admin.transactions.*', 'Deposits & Withdrawals', route('admin.transactions.index')],
        ['admin.users.*', 'Players', route('admin.users.index')],
        ['admin.payment-methods.*', 'Payment Methods', route('admin.payment-methods.index')],
        ['admin.settings.*', 'Settings', route('admin.settings.edit')],
    ];
@endphp
<div class="flex min-h-screen">
    <aside class="w-60 shrink-0 bg-zinc-900 border-r border-zinc-800 p-4 hidden md:block">
        <div class="text-xl font-bold text-brand mb-6">🔥 FF Tournament</div>
        <nav class="space-y-1">
            @foreach ($nav as [$pattern, $label, $url])
                <a href="{{ $url }}"
                   class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs($pattern) ? 'bg-brand text-white' : 'text-zinc-400 hover:bg-zinc-800 hover:text-white' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
        <form method="POST" action="{{ route('admin.logout') }}" class="mt-8">
            @csrf
            <button class="w-full text-left rounded-lg px-3 py-2 text-sm text-red-400 hover:bg-zinc-800">Log out</button>
        </form>
    </aside>

    <main class="flex-1 p-4 md:p-8 min-w-0">
        {{-- Mobile nav --}}
        <div class="md:hidden flex gap-2 overflow-x-auto pb-4">
            @foreach ($nav as [$pattern, $label, $url])
                <a href="{{ $url }}" class="whitespace-nowrap rounded-full border border-zinc-700 px-3 py-1 text-xs {{ request()->routeIs($pattern) ? 'bg-brand text-white border-brand' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-green-800 bg-green-950 px-4 py-3 text-sm text-green-300">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg border border-red-800 bg-red-950 px-4 py-3 text-sm text-red-300">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-800 bg-red-950 px-4 py-3 text-sm text-red-300">
                <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <h1 class="text-2xl font-semibold text-white mb-6">@yield('heading')</h1>
        @yield('content')
    </main>
</div>
</body>
</html>
