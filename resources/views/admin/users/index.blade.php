@extends('admin.layout')
@section('title', 'Players')
@section('heading', 'Players')

@section('content')
    <form method="GET" class="flex gap-2 mb-4 text-sm">
        <input name="q" value="{{ request('q') }}" placeholder="Search name, phone or UID"
               class="w-72 rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2">
        <button class="rounded-lg bg-orange-600 hover:bg-orange-700 text-white px-4 py-2">Search</button>
    </form>

    <div class="rounded-xl bg-zinc-900 border border-zinc-800 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-zinc-400 text-left"><tr>
                <th class="p-3">Name</th><th class="p-3">UID</th><th class="p-3">Phone</th>
                <th class="p-3">Balance</th><th class="p-3">Status</th><th class="p-3">Joined</th><th class="p-3"></th>
            </tr></thead>
            <tbody>
            @forelse ($users as $u)
                <tr class="border-t border-zinc-800">
                    <td class="p-3 text-white">{{ $u->name }}</td>
                    <td class="p-3">{{ $u->uid }}</td>
                    <td class="p-3">{{ $u->phone }}</td>
                    <td class="p-3">{{ number_format($u->balance) }} BDT</td>
                    <td class="p-3">{!! $u->is_blocked ? '<span class="text-red-400">Blocked</span>' : '<span class="text-green-400">Active</span>' !!}</td>
                    <td class="p-3 text-zinc-400">{{ $u->created_at->format('d M Y') }}</td>
                    <td class="p-3 text-right"><a href="{{ route('admin.users.show', $u) }}" class="text-orange-500 hover:underline">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-6 text-center text-zinc-500">No players found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
@endsection
