@extends('admin.layout')
@section('title', 'Tournaments')
@section('heading', 'Tournaments')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div class="flex flex-wrap items-center gap-2 text-sm">
            @foreach ([null => 'All', 'pending_approval' => '⏳ Awaiting Approval', 'active' => 'Active', 'upcoming' => 'Upcoming', 'completed' => 'Completed'] as $value => $label)
                <a href="{{ route('admin.tournaments.index', array_filter(['status' => $value, 'category_id' => request('category_id')])) }}"
                   class="rounded-xl px-3.5 py-1.5 text-xs font-semibold transition-all {{ request('status') == $value ? 'bg-brand text-white shadow-sm shadow-orange-500/20' : 'bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-50 shadow-sm' }}">{{ $label }}</a>
            @endforeach

            @if ($categories->isNotEmpty())
                <form method="GET" action="{{ route('admin.tournaments.index') }}" class="inline-block ml-1">
                    @if (request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <select name="category_id" onchange="this.form.submit()" class="rounded-xl px-3 py-1.5 text-xs font-semibold bg-white border border-zinc-200 text-zinc-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->title }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
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
                    <tr class="hover:bg-zinc-50/70 transition-colors {{ $t->status === 'pending_approval' ? 'bg-amber-50/30' : '' }}">
                        <td class="p-3.5 pl-5">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-zinc-900">{{ $t->title }}</span>
                                <span class="text-[11px] font-mono text-zinc-400 font-semibold">{{ $t->match_number }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="inline-flex items-center text-[10px] font-bold text-sky-700 bg-sky-50 border border-sky-200/60 rounded px-1.5 py-0.2">
                                    {{ $t->type ?? 'Solo' }}
                                </span>
                                @if ($t->category)
                                    <span class="inline-flex items-center text-[10px] font-semibold text-orange-600 bg-orange-50 border border-orange-200/70 rounded px-1.5 py-0.2">
                                        {{ $t->category->title }}
                                    </span>
                                @endif
                                @if ($t->moderator)
                                    <span class="inline-flex items-center text-[10px] font-medium text-purple-700 bg-purple-50 border border-purple-200/60 rounded px-1.5 py-0.2">
                                        Mod: {{ $t->moderator->name }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="p-3.5 font-medium text-zinc-700">{{ $t->map }}</td>
                        <td class="p-3.5 font-semibold text-zinc-800">{{ $t->entry_fee }} BDT</td>
                        <td class="p-3.5 font-bold text-zinc-900">{{ $t->prize }} BDT</td>
                        <td class="p-3.5 font-medium text-zinc-700">{{ $t->registrations_count }}/{{ $t->slots }}</td>
                        <td class="p-3.5 whitespace-nowrap text-zinc-600 text-xs">{{ $t->start_time->format('d M, g:i A') }}</td>
                        <td class="p-3.5">
                            @if ($t->status === 'pending_approval')
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 inline-flex items-center gap-1 shadow-2xs">
                                    <span>⏳</span> Awaiting Approval
                                </span>
                            @elseif ($t->status === 'active')
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Active
                                </span>
                            @elseif ($t->status === 'upcoming')
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    Upcoming
                                </span>
                            @else
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-zinc-100 text-zinc-700 border border-zinc-200">
                                    Completed
                                </span>
                            @endif
                        </td>
                        <td class="p-3.5 pr-5 whitespace-nowrap text-right space-x-2 text-xs">
                            @if ($t->status === 'pending_approval')
                                @if (auth()->user()?->is_admin)
                                    <a href="{{ route('admin.tournaments.results', $t) }}" class="inline-flex items-center font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 rounded-lg px-2.5 py-1 transition-all shadow-2xs">
                                        ⚡ Review & Approve
                                    </a>
                                @else
                                    <a href="{{ route('admin.tournaments.results', $t) }}" class="font-semibold text-amber-700 hover:underline">
                                        View Submitted
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('admin.tournaments.results', $t) }}" class="font-semibold text-brand hover:underline">Results</a>
                            @endif

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
