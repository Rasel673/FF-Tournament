@extends('admin.layout')
@section('title', 'Dashboard')
@section('heading', 'Dashboard Overview')

@section('content')
    @php
        $statMeta = [
            'Players' => [
                'color' => 'text-blue-600',
                'bg' => 'bg-blue-50 border-blue-200/80',
                'icon' => '<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
            ],
            'Active tournaments' => [
                'color' => 'text-emerald-600',
                'bg' => 'bg-emerald-50 border-emerald-200/80',
                'icon' => '<svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>',
            ],
            'Pending deposits' => [
                'color' => 'text-amber-600',
                'bg' => 'bg-amber-50 border-amber-200/80',
                'icon' => '<svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>',
            ],
            'Pending withdrawals' => [
                'color' => 'text-rose-600',
                'bg' => 'bg-rose-50 border-rose-200/80',
                'icon' => '<svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>',
            ],
            'Total deposited (BDT)' => [
                'color' => 'text-orange-600',
                'bg' => 'bg-orange-50 border-orange-200/80',
                'icon' => '<svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            ],
            'Total wallet balance (BDT)' => [
                'color' => 'text-indigo-600',
                'bg' => 'bg-indigo-50 border-indigo-200/80',
                'icon' => '<svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
            ],
        ];
    @endphp

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5 mb-8">
        @foreach ($stats as $label => $value)
            @php $meta = $statMeta[$label] ?? ['bg' => 'bg-slate-50 border-slate-200', 'icon' => '']; @endphp
            <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition-all flex items-start justify-between">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 tracking-tight">{{ number_format($value) }}</div>
                </div>
                <div class="h-11 w-11 rounded-xl border {{ $meta['bg'] }} flex items-center justify-center shrink-0 shadow-xs">
                    {!! $meta['icon'] !!}
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pending Requests Section -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <h2 class="text-lg font-bold text-slate-900">Pending Requests</h2>
                @if ($pending->count() > 0)
                    <span class="rounded-full bg-amber-100 text-amber-800 text-xs font-bold px-2.5 py-0.5 border border-amber-200 shadow-xs">
                        {{ $pending->count() }}
                    </span>
                @endif
            </div>
            <a href="{{ route('admin.transactions.index', ['status' => 'pending']) }}"
               class="text-xs sm:text-sm font-semibold text-brand hover:text-brand-dark transition-colors inline-flex items-center gap-1">
                Review all pending &rarr;
            </a>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200 tracking-wider">
                        <tr>
                            <th class="p-3.5 pl-5">Player</th>
                            <th class="p-3.5">Type</th>
                            <th class="p-3.5">Method</th>
                            <th class="p-3.5">Amount</th>
                            <th class="p-3.5 pr-5">When</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse ($pending as $tx)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="p-3.5 pl-5">
                                <a href="{{ route('admin.users.show', $tx->user_id) }}" class="font-semibold text-slate-900 hover:text-brand transition-colors">
                                    {{ $tx->user->name }}
                                </a>
                            </td>
                            <td class="p-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $tx->type === 'deposit' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-rose-50 text-rose-700 border border-rose-200/80' }}">
                                    {{ ucfirst($tx->type) }}
                                </span>
                            </td>
                            <td class="p-3.5 font-medium text-slate-700">{{ $tx->method ?? '-' }}</td>
                            <td class="p-3.5 font-extrabold text-slate-900">{{ number_format($tx->amount) }} BDT</td>
                            <td class="p-3.5 pr-5 text-slate-500 text-xs">{{ $tx->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-10 text-center">
                                <div class="text-3xl mb-2">🎉</div>
                                <div class="font-semibold text-slate-800 text-base">Nothing pending right now</div>
                                <div class="text-xs text-slate-500 mt-1">All player deposits and withdrawals have been processed.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
