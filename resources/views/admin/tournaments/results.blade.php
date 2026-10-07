@extends('admin.layout')
@section('title', 'Results')
@section('heading', 'Results · '.$tournament->title)

@section('content')
    @php
        $done = $tournament->status === 'completed';
        $input = 'rounded-lg bg-white border border-zinc-300 text-zinc-900 px-2.5 py-1.5 w-24 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand shadow-sm';
    @endphp

    <div class="rounded-2xl bg-orange-50/80 border border-orange-200/80 p-4 mb-6 text-sm text-orange-950 flex items-start gap-3 shadow-sm">
        <span class="text-lg">ℹ️</span>
        <div>
            Total prize = <b class="text-orange-900">kills × {{ $tournament->per_kill }} BDT</b> + extra position prize.
            Prizes are credited to player wallets once upon publishing. After publishing you can update the note and proof screenshot.
        </div>
    </div>

    <form method="POST" action="{{ route('admin.tournaments.results.store', $tournament) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-zinc-50 text-zinc-600 text-xs uppercase font-semibold border-b border-zinc-200 tracking-wider">
                        <tr>
                            <th class="p-3.5 pl-5">Player</th>
                            <th class="p-3.5">UID</th>
                            <th class="p-3.5">Position</th>
                            <th class="p-3.5">Kills</th>
                            <th class="p-3.5 pr-5">Position Prize</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                    @if ($done)
                        @forelse ($results as $r)
                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                <td class="p-3.5 pl-5 font-semibold text-zinc-900">{{ $r->user->name }}</td>
                                <td class="p-3.5 font-medium text-zinc-600">{{ $r->user->uid }}</td>
                                <td class="p-3.5 font-semibold text-zinc-800">{{ $r->position ?? '-' }}</td>
                                <td class="p-3.5 font-semibold text-zinc-800">{{ $r->kills }}</td>
                                <td class="p-3.5 pr-5 font-extrabold text-brand">Total: {{ $r->prize }} BDT</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-8 text-center text-zinc-500 font-medium">No results were entered.</td></tr>
                        @endforelse
                    @else
                        @forelse ($registrations as $reg)
                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                <td class="p-3.5 pl-5 font-semibold text-zinc-900">{{ $reg->user->name }}</td>
                                <td class="p-3.5 font-medium text-zinc-600">{{ $reg->user->uid }}</td>
                                <td class="p-3.5"><input type="number" min="1" name="results[{{ $reg->user_id }}][position]" class="{{ $input }}"></td>
                                <td class="p-3.5"><input type="number" min="0" name="results[{{ $reg->user_id }}][kills]" value="0" class="{{ $input }}"></td>
                                <td class="p-3.5 pr-5"><input type="number" min="0" name="results[{{ $reg->user_id }}][prize]" value="0" class="{{ $input }}"></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-8 text-center text-zinc-500 font-medium">No players joined yet.</td></tr>
                        @endforelse
                    @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-6 space-y-4 max-w-xl">
            <div>
                <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Note shown in the app</label>
                <textarea name="result_note" rows="3" class="w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2 text-zinc-900 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-brand">{{ old('result_note', $tournament->result_note) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Payment proof / result screenshot</label>
                @if ($tournament->proof_image)
                    <a href="{{ asset('storage/'.$tournament->proof_image) }}" target="_blank">
                        <img src="{{ asset('storage/'.$tournament->proof_image) }}" class="h-32 rounded-xl border border-zinc-200 shadow-sm mb-2.5 object-cover" alt="proof">
                    </a>
                @endif
                <input type="file" name="proof_image" accept="image/*" class="text-sm text-zinc-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200">
            </div>
        </div>

        <button class="rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold px-6 py-2.5 shadow-sm shadow-orange-500/20 transition-all"
                @unless($done) onclick="return confirm('Publish results and credit prizes? This cannot be repeated.')" @endunless>
            {{ $done ? 'Update note & proof' : 'Publish results & pay prizes' }}
        </button>
    </form>
@endsection
