@extends('admin.layout')
@section('title', 'Tournaments')
@section('heading', 'Tournaments')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex gap-2 text-sm">
            @foreach ([null => 'All', 'active' => 'Active', 'upcoming' => 'Upcoming', 'completed' => 'Completed'] as $value => $label)
                <a href="{{ route('admin.tournaments.index', $value ? ['status' => $value] : []) }}"
                   class="rounded-full border px-3 py-1 {{ request('status') == $value ? 'bg-orange-600 border-orange-600 text-white' : 'border-zinc-700 text-zinc-400' }}">{{ $label }}</a>
            @endforeach
        </div>
        <a href="{{ route('admin.tournaments.create') }}" class="rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm px-4 py-2">+ New tournament</a>
    </div>

    <div class="rounded-xl bg-zinc-900 border border-zinc-800 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-zinc-400 text-left"><tr>
                <th class="p-3">Title</th><th class="p-3">Map</th><th class="p-3">Entry</th><th class="p-3">Prize</th>
                <th class="p-3">Slots</th><th class="p-3">Starts</th><th class="p-3">Status</th><th class="p-3"></th>
            </tr></thead>
            <tbody>
            @forelse ($tournaments as $t)
                <tr class="border-t border-zinc-800">
                    <td class="p-3 text-white">{{ $t->title }}</td>
                    <td class="p-3">{{ $t->map }}</td>
                    <td class="p-3">{{ $t->entry_fee }} BDT</td>
                    <td class="p-3">{{ $t->prize }} BDT</td>
                    <td class="p-3">{{ $t->registrations_count }}/{{ $t->slots }}</td>
                    <td class="p-3 whitespace-nowrap">{{ $t->start_time->format('d M, g:i A') }}</td>
                    <td class="p-3">
                        <span class="rounded-full px-2 py-0.5 text-xs
                            {{ ['active' => 'bg-green-900 text-green-300', 'upcoming' => 'bg-yellow-900 text-yellow-300', 'completed' => 'bg-orange-950 text-orange-400'][$t->status] }}">
                            {{ ucfirst($t->status) }}
                        </span>
                    </td>
                    <td class="p-3 whitespace-nowrap text-right space-x-3">
                        <a href="{{ route('admin.tournaments.results', $t) }}" class="text-orange-500 hover:underline">Results</a>
                        <a href="{{ route('admin.tournaments.edit', $t) }}" class="text-zinc-300 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.tournaments.destroy', $t) }}" class="inline" onsubmit="return confirm('Delete this tournament?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="p-6 text-center text-zinc-500">No tournaments yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $tournaments->links() }}</div>
@endsection
