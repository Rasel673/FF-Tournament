@extends('admin.layout')
@section('title', 'Players')
@section('heading', 'Players')

@section('content')
    <form method="GET" class="flex gap-2.5 mb-5 text-sm items-center">
        <input name="q" value="{{ request('q') }}" placeholder="Search name, phone or UID..."
               class="w-72 sm:w-80 rounded-xl bg-white border border-zinc-300 px-3.5 py-2 text-zinc-900 placeholder-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand">
        <button class="rounded-xl bg-brand hover:bg-brand-dark text-white px-5 py-2 font-semibold shadow-sm shadow-orange-500/20 transition-all">Search</button>
        @if (request('q'))
            <a href="{{ route('admin.users.index') }}" class="text-xs text-zinc-500 hover:text-zinc-800 ml-1">Clear</a>
        @endif
    </form>

    <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-zinc-50 text-zinc-600 text-xs uppercase font-semibold border-b border-zinc-200 tracking-wider">
                    <tr>
                        <th class="p-3.5 pl-5">Name</th>
                        <th class="p-3.5">UID</th>
                        <th class="p-3.5">Phone</th>
                        <th class="p-3.5">Balance</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5">Joined</th>
                        <th class="p-3.5 pr-5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                @forelse ($users as $u)
                    <tr class="hover:bg-zinc-50/70 transition-colors">
                        <td class="p-3.5 pl-5 font-semibold text-zinc-900">{{ $u->name }}</td>
                        <td class="p-3.5 font-mono text-xs text-zinc-700">{{ $u->uid }}</td>
                        <td class="p-3.5 font-medium text-zinc-700">{{ $u->phone }}</td>
                        <td class="p-3.5 font-extrabold text-zinc-900">{{ number_format($u->balance) }} BDT</td>
                        <td class="p-3.5">
                            {!! $u->is_blocked
                                ? '<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">Blocked</span>'
                                : '<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>' !!}
                        </td>
                        <td class="p-3.5 whitespace-nowrap text-zinc-500 text-xs">{{ $u->created_at->format('d M Y') }}</td>
                        <td class="p-3.5 pr-5 text-right">
                            <a href="{{ route('admin.users.show', $u) }}" class="font-semibold text-brand hover:underline text-xs">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-zinc-500 font-medium">No players found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
@endsection
