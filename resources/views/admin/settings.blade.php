@extends('admin.layout')
@section('title', 'Settings')
@section('heading', 'Settings')

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-xl rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-6 sm:p-8 space-y-5">
        @csrf
        @foreach ($fields as $key => $label)
            <div>
                <label class="block text-sm font-semibold text-zinc-700 mb-1.5">{{ $label }}</label>
                <input name="{{ $key }}" value="{{ old($key, $values[$key]) }}"
                       @if (str_starts_with($key, 'min_')) type="number" min="1" required @endif
                       class="w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2.5 text-zinc-900 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-brand placeholder-zinc-400">
            </div>
        @endforeach
        <button class="rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold px-6 py-2.5 shadow-sm shadow-orange-500/20 transition-all">Save Settings</button>
    </form>
@endsection
