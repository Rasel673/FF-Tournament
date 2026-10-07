@extends('admin.layout')
@php $editing = $tournament->exists; @endphp
@section('title', $editing ? 'Edit tournament' : 'New tournament')
@section('heading', $editing ? 'Edit tournament' : 'New tournament')

@section('content')
    @php
        $input = 'w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 focus:outline-none focus:border-orange-600';
    @endphp
    <form method="POST" action="{{ $editing ? route('admin.tournaments.update', $tournament) : route('admin.tournaments.store') }}"
          class="max-w-3xl rounded-xl bg-zinc-900 border border-zinc-800 p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="md:col-span-2">
            <label class="block text-sm mb-1">Title</label>
            <input name="title" value="{{ old('title', $tournament->title) }}" required class="{{ $input }}" placeholder="BR Rank Push">
        </div>
        <div>
            <label class="block text-sm mb-1">Map</label>
            <input name="map" value="{{ old('map', $tournament->map) }}" required class="{{ $input }}" placeholder="Bermuda">
        </div>
        <div>
            <label class="block text-sm mb-1">Status</label>
            <select name="status" class="{{ $input }}">
                @foreach (['upcoming', 'active', 'completed'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $tournament->status) === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm mb-1">Entry fee (BDT)</label>
            <input type="number" min="0" name="entry_fee" value="{{ old('entry_fee', $tournament->entry_fee) }}" required class="{{ $input }}">
        </div>
        <div>
            <label class="block text-sm mb-1">Prize pool (BDT)</label>
            <input type="number" min="0" name="prize" value="{{ old('prize', $tournament->prize) }}" required class="{{ $input }}">
        </div>
        <div>
            <label class="block text-sm mb-1">Per kill (BDT)</label>
            <input type="number" min="0" name="per_kill" value="{{ old('per_kill', $tournament->per_kill) }}" required class="{{ $input }}">
        </div>
        <div>
            <label class="block text-sm mb-1">Total slots</label>
            <input type="number" min="1" name="slots" value="{{ old('slots', $tournament->slots) }}" required class="{{ $input }}">
        </div>
        <div>
            <label class="block text-sm mb-1">Match start time</label>
            <input type="datetime-local" name="start_time" value="{{ old('start_time', $tournament->start_time?->format('Y-m-d\TH:i')) }}" required class="{{ $input }}">
        </div>
        <div>
            <label class="block text-sm mb-1">Registration opens at <span class="text-zinc-500">(shown for upcoming)</span></label>
            <input type="datetime-local" name="open_time" value="{{ old('open_time', $tournament->open_time?->format('Y-m-d\TH:i')) }}" class="{{ $input }}">
        </div>
        <div>
            <label class="block text-sm mb-1">Room ID</label>
            <input name="room_id" value="{{ old('room_id', $tournament->room_id) }}" class="{{ $input }}">
        </div>
        <div>
            <label class="block text-sm mb-1">Room password</label>
            <input name="room_password" value="{{ old('room_password', $tournament->room_password) }}" class="{{ $input }}">
        </div>

        <div class="md:col-span-2 flex gap-3 pt-2">
            <button class="rounded-lg bg-orange-600 hover:bg-orange-700 text-white px-5 py-2">Save</button>
            <a href="{{ route('admin.tournaments.index') }}" class="rounded-lg border border-zinc-700 px-5 py-2">Cancel</a>
        </div>
    </form>
@endsection
