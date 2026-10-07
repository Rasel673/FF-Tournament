@extends('admin.layout')
@php $editing = $tournament->exists; @endphp
@section('title', $editing ? 'Edit tournament' : 'New tournament')
@section('heading', $editing ? 'Edit tournament' : 'New tournament')

@section('content')
    @php
        $input = 'w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2.5 text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand shadow-sm text-sm transition-all';
    @endphp
    <form method="POST" action="{{ $editing ? route('admin.tournaments.update', $tournament) : route('admin.tournaments.store') }}"
          class="max-w-3xl rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-5">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Title</label>
            <input name="title" value="{{ old('title', $tournament->title) }}" required class="{{ $input }}" placeholder="BR Rank Push">
        </div>
        <div>
            <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Map</label>
            <input name="map" value="{{ old('map', $tournament->map) }}" required class="{{ $input }}" placeholder="Bermuda">
        </div>
        <div>
            <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Status</label>
            <select name="status" class="{{ $input }}">
                @foreach (['upcoming', 'active', 'completed'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $tournament->status) === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Entry fee (BDT)</label>
            <input type="number" min="0" name="entry_fee" value="{{ old('entry_fee', $tournament->entry_fee) }}" required class="{{ $input }}">
        </div>
        <div>
            <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Prize pool (BDT)</label>
            <input type="number" min="0" name="prize" value="{{ old('prize', $tournament->prize) }}" required class="{{ $input }}">
        </div>
        <div>
            <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Per kill (BDT)</label>
            <input type="number" min="0" name="per_kill" value="{{ old('per_kill', $tournament->per_kill) }}" required class="{{ $input }}">
        </div>
        <div>
            <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Total slots</label>
            <input type="number" min="1" name="slots" value="{{ old('slots', $tournament->slots) }}" required class="{{ $input }}">
        </div>
        <div>
            <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Match start time</label>
            <input type="datetime-local" name="start_time" value="{{ old('start_time', $tournament->start_time?->format('Y-m-d\TH:i')) }}" required class="{{ $input }}">
        </div>
        <div>
            <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Registration opens at <span class="text-zinc-500 font-normal">(shown for upcoming)</span></label>
            <input type="datetime-local" name="open_time" value="{{ old('open_time', $tournament->open_time?->format('Y-m-d\TH:i')) }}" class="{{ $input }}">
        </div>
        <div>
            <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Room ID</label>
            <input name="room_id" value="{{ old('room_id', $tournament->room_id) }}" class="{{ $input }}">
        </div>
        <div>
            <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Room password</label>
            <input name="room_password" value="{{ old('room_password', $tournament->room_password) }}" class="{{ $input }}">
        </div>

        <div class="md:col-span-2 flex items-center gap-3 pt-3">
            <button class="rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold px-6 py-2.5 shadow-sm shadow-orange-500/20 transition-all">Save tournament</button>
            <a href="{{ route('admin.tournaments.index') }}" class="rounded-xl bg-white border border-zinc-300 text-zinc-700 hover:bg-zinc-50 font-semibold px-6 py-2.5 shadow-sm transition-all">Cancel</a>
        </div>
    </form>
@endsection
