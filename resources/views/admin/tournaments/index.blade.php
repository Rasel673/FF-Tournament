@extends('admin.layout')
@section('title', 'Tournaments')
@section('heading', 'Tournaments')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div class="flex flex-wrap gap-2 text-sm">
            @foreach ([null => 'All', 'active' => 'Active', 'upcoming' => 'Upcoming', 'completed' => 'Completed'] as $value => $label)
                <a href="{{ route('admin.tournaments.index', $value ? ['status' => $value] : []) }}"
                   class="rounded-xl px-3.5 py-1.5 text-xs font-semibold transition-all {{ request('status') == $value ? 'bg-brand text-white shadow-sm shadow-orange-500/20' : 'bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-50 shadow-sm' }}">{{ $label }}</a>
            @endforeach
        </div>
        <a href="{{ route('admin.tournaments.create') }}" class="rounded-xl bg-brand hover:bg-brand-dark text-white text-sm font-semibold px-4 py-2 shadow-sm shadow-orange-500/20 transition-all flex items-center gap-1.5">
            <span>+</span> New tournament
        </a>
    </div>

    <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-zinc-50 text-zinc-600 text-xs uppercase font-semibold border-b border-zinc-200 tracking-wider">
                    <tr>
                        <th class="p-3.5 pl-5">Title</th>
                        <th class="p-3.5">Map</th>
                        <th class="p-3.5">Entry</th>
                        <th class="p-3.5">Prize</th>
                        <th class="p-3.5">Slots</th>
                        <th class="p-3.5">Starts</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 pr-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                @forelse ($tournaments as $t)
                    <tr class="hover:bg-zinc-50/70 transition-colors">
                        <td class="p-3.5 pl-5 font-semibold text-zinc-900">{{ $t->title }}</td>
                        <td class="p-3.5 font-medium text-zinc-700">{{ $t->map }}</td>
                        <td class="p-3.5 font-semibold text-zinc-800">{{ $t->entry_fee }} BDT</td>
                        <td class="p-3.5 font-bold text-zinc-900">{{ $t->prize }} BDT</td>
                        <td class="p-3.5 font-medium text-zinc-700">{{ $t->registrations_count }}/{{ $t->slots }}</td>
                        <td class="p-3.5 whitespace-nowrap text-zinc-600 text-xs">{{ $t->start_time->format('d M, g:i A') }}</td>
                        <td class="p-3.5">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold
                                {{ ['active' => 'bg-emerald-50 text-emerald-700 border border-emerald-200', 'upcoming' => 'bg-amber-50 text-amber-700 border border-amber-200', 'completed' => 'bg-zinc-100 text-zinc-700 border border-zinc-200'][$t->status] }}">
                                {{ ucfirst($t->status) }}
                            </span>
                        </td>
                        <td class="p-3.5 pr-5 whitespace-nowrap text-right space-x-3 text-xs">
                            <a href="{{ route('admin.tournaments.results', $t) }}" class="font-semibold text-brand hover:underline">Results</a>
                            <a href="{{ route('admin.tournaments.edit', $t) }}" class="font-medium text-zinc-700 hover:text-zinc-900 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.tournaments.destroy', $t) }}" class="inline" onsubmit="return confirm('Delete this tournament?')">
                                @csrf @method('DELETE')
                                <button class="font-medium text-rose-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-zinc-500 font-medium">No tournaments found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $tournaments->links() }}</div>
@endsection
