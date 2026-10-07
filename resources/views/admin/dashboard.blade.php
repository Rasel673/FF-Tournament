@extends('admin.layout')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @foreach ($stats as $label => $value)
            <div class="rounded-xl bg-zinc-900 border border-zinc-800 p-4">
                <div class="text-sm text-zinc-400">{{ $label }}</div>
                <div class="text-2xl font-semibold text-white mt-1">{{ number_format($value) }}</div>
            </div>
        @endforeach
    </div>

    <h2 class="text-lg font-medium text-white mb-3">Pending requests</h2>
    <div class="rounded-xl bg-zinc-900 border border-zinc-800 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-zinc-400 text-left"><tr>
                <th class="p-3">Player</th><th class="p-3">Type</th><th class="p-3">Method</th><th class="p-3">Amount</th><th class="p-3">When</th>
            </tr></thead>
            <tbody>
            @forelse ($pending as $tx)
                <tr class="border-t border-zinc-800">
                    <td class="p-3">{{ $tx->user->name }}</td>
                    <td class="p-3 capitalize">{{ $tx->type }}</td>
                    <td class="p-3">{{ $tx->method }}</td>
                    <td class="p-3">{{ number_format($tx->amount) }} BDT</td>
                    <td class="p-3 text-zinc-400">{{ $tx->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-6 text-center text-zinc-500">Nothing pending 🎉</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <a href="{{ route('admin.transactions.index', ['status' => 'pending']) }}" class="inline-block mt-3 text-sm text-orange-500 hover:underline">Review all pending →</a>
@endsection
