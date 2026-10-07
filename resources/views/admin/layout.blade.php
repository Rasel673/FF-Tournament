<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · FF Tournament</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Tailwind via CDN keeps setup zero-build. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            DEFAULT: '#E0491A',
                            dark: '#B93A12',
                            light: '#FF6B3D',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen antialiased flex flex-col md:flex-row font-sans">
@php
    $nav = [
        [
            'pattern' => 'admin.dashboard',
            'label' => 'Dashboard',
            'url' => route('admin.dashboard'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
        ],
        [
            'pattern' => 'admin.tournaments.*',
            'label' => 'Tournaments',
            'url' => route('admin.tournaments.index'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>',
        ],
        [
            'pattern' => 'admin.transactions.*',
            'label' => 'Deposits & Withdrawals',
            'url' => route('admin.transactions.index'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
        ],
        [
            'pattern' => 'admin.users.*',
            'label' => 'Players',
            'url' => route('admin.users.index'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
        ],
        [
            'pattern' => 'admin.payment-methods.*',
            'label' => 'Payment Methods',
            'url' => route('admin.payment-methods.index'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
        ],
        [
            'pattern' => 'admin.settings.*',
            'label' => 'Settings',
            'url' => route('admin.settings.edit'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        ],
    ];
@endphp

<!-- Premium Light Sidebar (Desktop) -->
<aside class="w-64 shrink-0 bg-white border-r border-slate-200/90 text-slate-700 hidden md:flex md:flex-col md:h-screen md:sticky md:top-0 z-30 select-none shadow-sm">
    <!-- Brand Header -->
    <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-orange-50/60 via-white to-transparent">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-brand via-orange-500 to-amber-500 flex items-center justify-center text-white text-xl shadow-md shadow-orange-500/25 ring-2 ring-orange-100">
                🔥
            </div>
            <div>
                <div class="font-extrabold text-slate-900 text-base leading-tight tracking-tight">FF Tournament</div>
                <div class="text-[10px] uppercase font-bold tracking-wider text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full border border-orange-200/70 inline-block mt-0.5">Admin Portal</div>
            </div>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto">
        @foreach ($nav as $item)
            @php $isActive = request()->routeIs($item['pattern']); @endphp
            <a href="{{ $item['url'] }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group {{ $isActive ? 'bg-gradient-to-r from-brand via-orange-600 to-amber-500 text-white font-semibold shadow-md shadow-orange-500/25 ring-1 ring-orange-400/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <span class="shrink-0 transition-colors {{ $isActive ? 'text-white' : 'text-slate-400 group-hover:text-slate-700' }}">{!! $item['icon'] !!}</span>
                <span class="truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <!-- Bottom User / Logout Card -->
    <div class="p-3 border-t border-slate-100 bg-slate-50/50">
        <div class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="h-8 w-8 rounded-lg bg-gradient-to-tr from-brand to-amber-500 flex items-center justify-center text-white font-bold text-xs ring-2 ring-orange-100 shadow-xs shrink-0">
                    A
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-900 truncate">Administrator</div>
                    <div class="text-[10px] text-slate-500 truncate">admin@example.com</div>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" class="shrink-0">
                @csrf
                <button title="Log out" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Main Wrapper -->
<div class="flex-1 flex flex-col min-w-0 min-h-screen bg-slate-100">
    <!-- Premium Light Header -->
    <header class="bg-white border-b border-slate-200/90 text-slate-800 sticky top-0 z-20 px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between shadow-xs">
        <!-- Left: Mobile Menu Toggle & Title -->
        <div class="flex items-center gap-3">
            <!-- Mobile Menu Toggle Button -->
            <button id="mobileMenuBtn" type="button" class="md:hidden p-2 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 focus:outline-none transition-colors" aria-label="Toggle menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <!-- Brand on Mobile -->
            <div class="flex items-center gap-2 md:hidden">
                <span class="text-lg">🔥</span>
                <span class="font-bold text-slate-900 text-sm">FF Tournament</span>
            </div>

            <!-- Page breadcrumb / title indicator on Desktop -->
            <div class="hidden md:flex items-center gap-2 text-sm">
                <span class="text-slate-500 font-medium">Admin</span>
                <span class="text-slate-300">/</span>
                <span class="text-slate-900 font-bold tracking-tight">@yield('title', 'Dashboard')</span>
            </div>
        </div>

        <!-- Right: Status Pill & Profile -->
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="hidden sm:inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>System Active</span>
            </div>

            <div class="flex items-center gap-3 pl-3 sm:border-l sm:border-slate-200">
                <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-brand via-orange-500 to-amber-500 text-white flex items-center justify-center font-bold text-xs ring-2 ring-orange-100 shadow-xs">
                    A
                </div>
                <div class="hidden sm:block text-left">
                    <div class="text-xs font-bold text-slate-900 leading-tight">Admin</div>
                    <div class="text-[10px] text-slate-500 leading-tight">Superadmin</div>
                </div>

                <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-rose-600 hover:bg-rose-50 px-2.5 py-1.5 rounded-lg transition-colors font-medium ml-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation Drawer (Light Theme) -->
    <div id="mobileDrawer" class="hidden md:hidden bg-white border-b border-slate-200 px-4 py-3 space-y-1 shadow-md">
        @foreach ($nav as $item)
            @php $isActive = request()->routeIs($item['pattern']); @endphp
            <a href="{{ $item['url'] }}"
               class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium {{ $isActive ? 'bg-gradient-to-r from-brand to-amber-500 text-white font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="shrink-0">{!! $item['icon'] !!}</span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
        <form method="POST" action="{{ route('admin.logout') }}" class="pt-2 border-t border-slate-100 mt-2">
            @csrf
            <button class="w-full text-left rounded-xl px-3 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Log out
            </button>
        </form>
    </div>

    <!-- Light Theme Dashboard Content Area -->
    <main class="flex-1 p-4 sm:p-6 lg:p-8 min-w-0 bg-slate-100 text-slate-800">
        @if (session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2.5 shadow-sm">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800 flex items-center gap-2.5 shadow-sm">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm">
                <div class="font-bold flex items-center gap-2 mb-1">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span>Please correct the errors below:</span>
                </div>
                <ul class="list-disc pl-7 text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">@yield('heading')</h1>
        </div>

        @yield('content')
    </main>
</div>

<script>
    // Mobile navigation toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileDrawer = document.getElementById('mobileDrawer');
    if (mobileMenuBtn && mobileDrawer) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileDrawer.classList.toggle('hidden');
        });
    }
</script>
</body>
</html>
