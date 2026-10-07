<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-950 text-zinc-200 min-h-screen flex items-center justify-center p-4">
<form method="POST" action="{{ route('admin.login.submit') }}" class="w-full max-w-sm bg-zinc-900 border border-zinc-800 rounded-2xl p-6 space-y-4">
    @csrf
    <h1 class="text-xl font-bold text-white">🔥 FF Tournament Admin</h1>

    @if ($errors->any())
        <div class="rounded-lg bg-red-950 border border-red-800 px-3 py-2 text-sm text-red-300">{{ $errors->first() }}</div>
    @endif

    <div>
        <label class="block text-sm mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus
               class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 focus:outline-none focus:border-orange-600">
    </div>
    <div>
        <label class="block text-sm mb-1">Password</label>
        <input type="password" name="password" required
               class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 focus:outline-none focus:border-orange-600">
    </div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember"> Remember me</label>
    <button class="w-full rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-medium py-2">Sign in</button>
</form>
</body>
</html>
