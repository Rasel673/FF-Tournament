@extends('admin.layout')
@section('title', 'Results')
@section('heading', 'Results · '.$tournament->title)

@section('content')
    @php
        $done = $tournament->status === 'completed';
        $input = 'rounded-lg bg-zinc-800 border border-zinc-700 px-2 py-1 w-24 focus:outline-none focus:border-orange-600';
    @endphp

    <p class="text-sm text-zinc-400 mb-4">
        Total prize = <b>kills × {{ $tournament->per_kill }} BDT</b> + extra position prize. Prizes are credited to wallets once,
        when you publish. After that you can only update the note and proof image.
    </p>

    <form method="POST" action="{{ route('admin.tournaments.results.store', $tournament) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="rounded-xl bg-zinc-900 border border-zinc-800 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-zinc-400 text-left"><tr>
                    <th class="p-3">Player</th><th class="p-3">UID</th><th class="p-3">Position</th>
                    <th class="p-3">Kills</th><th class="p-3">Position prize</th>
                </tr></thead>
                <tbody>
                @if ($done)
                    @forelse ($results as $r)
                        <tr class="border-t border-zinc-800">
                            <td class="p-3">{{ $r->user->name }}</td><td class="p-3">{{ $r->user->uid }}</td>
                            <td class="p-3">{{ $r->position ?? '-' }}</td><td class="p-3">{{ $r->kills }}</td>
                            <td class="p-3">Total: {{ $r->prize }} BDT</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-zinc-500">No results were entered.</td></tr>
                    @endforelse
                @else
                    @forelse ($registrations as $reg)
                        <tr class="border-t border-zinc-800">
                            <td class="p-3">{{ $reg->user->name }}</td>
                            <td class="p-3">{{ $reg->user->uid }}</td>
                            <td class="p-3"><input type="number" min="1" name="results[{{ $reg->user_id }}][position]" class="{{ $input }}"></td>
                            <td class="p-3"><input type="number" min="0" name="results[{{ $reg->user_id }}][kills]" value="0" class="{{ $input }}"></td>
                            <td class="p-3"><input type="number" min="0" name="results[{{ $reg->user_id }}][prize]" value="0" class="{{ $input }}"></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-zinc-500">No players joined yet.</td></tr>
                    @endforelse
                @endif
                </tbody>
            </table>
        </div>

        <div class="rounded-xl bg-zinc-900 border border-zinc-800 p-4 space-y-4 max-w-xl">
            <div>
                <label class="block text-sm mb-1">Note shown in the app</label>
                <textarea name="result_note" rows="3" class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2">{{ old('result_note', $tournament->result_note) }}</textarea>
            </div>
            <div>
                <label class="block text-sm mb-1">Payment proof / result screenshot</label>
                @if ($tournament->proof_image)
                    <a href="{{ asset('storage/'.$tournament->proof_image) }}" target="_blank">
                        <img src="{{ asset('storage/'.$tournament->proof_image) }}" class="h-32 rounded-lg mb-2" alt="proof">
                    </a>
                @endif
                <input type="file" name="proof_image" accept="image/*" class="text-sm">
            </div>
        </div>

        <button class="rounded-lg bg-orange-600 hover:bg-orange-700 text-white px-5 py-2"
                @unless($done) onclick="return confirm('Publish results and credit prizes? This cannot be repeated.')" @endunless>
            {{ $done ? 'Update note & proof' : 'Publish results & pay prizes' }}
        </button>
    </form>
@endsection
