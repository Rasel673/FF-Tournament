@extends('admin.layout')
@section('title', 'Settings')
@section('heading', 'Settings')

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-xl rounded-xl bg-zinc-900 border border-zinc-800 p-6 space-y-4">
        @csrf
        @foreach ($fields as $key => $label)
            <div>
                <label class="block text-sm mb-1">{{ $label }}</label>
                <input name="{{ $key }}" value="{{ old($key, $values[$key]) }}"
                       @if (str_starts_with($key, 'min_')) type="number" min="1" required @endif
                       class="w-full rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 focus:outline-none focus:border-orange-600">
            </div>
        @endforeach
        <button class="rounded-lg bg-orange-600 hover:bg-orange-700 text-white px-5 py-2">Save settings</button>
    </form>
@endsection
