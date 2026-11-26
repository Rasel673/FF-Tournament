<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin & Moderator Login · FF Tournament</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4 font-sans antialiased">
<div class="w-full max-w-md">
    <!-- Brand Header -->
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center h-14 w-14 rounded-2xl bg-gradient-to-tr from-brand via-orange-500 to-amber-500 text-white text-3xl shadow-lg shadow-orange-500/30 mb-3 ring-4 ring-orange-500/20">
            🔥
        </div>
        <h1 class="text-2xl font-extrabold text-white tracking-tight">FF Tournament</h1>
        <p class="text-xs text-slate-400 mt-1">Admin & Moderator Control Portal</p>
    </div>

    <!-- Login Card -->
    <form method="POST" action="{{ route('admin.login.submit') }}" class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-6 sm:p-8 space-y-5 shadow-2xl backdrop-blur-xl">
        @csrf

        <div>
            <h2 class="text-lg font-bold text-white">Sign In</h2>
            <p class="text-xs text-slate-400 mt-0.5">Admin or registered moderator account</p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl bg-rose-500/10 border border-rose-500/30 px-3.5 py-2.5 text-xs text-rose-300 flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Email or Phone Number / ইমেইল বা ফোন নম্বর</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <input type="text" name="login" value="{{ old('login', old('email')) }}" required autofocus
                       placeholder="e.g. 018XXXXXXXX or admin@example.com"
                       class="w-full rounded-xl bg-slate-900/90 border border-slate-700 pl-10 pr-3.5 py-2.5 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand transition-all">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Password / পাসওয়ার্ড</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <input type="password" name="password" required
                       placeholder="Enter your password"
                       class="w-full rounded-xl bg-slate-900/90 border border-slate-700 pl-10 pr-3.5 py-2.5 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand transition-all">
            </div>
        </div>

        <div class="flex items-center justify-between text-xs text-slate-400">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-700 text-brand focus:ring-brand">
                <span>Remember me</span>
            </label>
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-gradient-to-r from-brand via-orange-600 to-amber-500 hover:from-brand-dark hover:to-orange-600 text-white font-bold py-3 px-4 text-xs tracking-wide shadow-lg shadow-orange-500/25 transition-all flex items-center justify-center gap-2">
            <span>Sign In to Portal</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>

        <div class="pt-3 border-t border-slate-700/60 text-center">
            <p class="text-[11px] text-slate-500">Moderators: Use your registered phone number & password</p>
        </div>
    </form>
</div>
</body>
</html>
