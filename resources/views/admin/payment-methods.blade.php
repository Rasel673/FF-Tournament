@extends('admin.layout')
@section('title', 'Payment Methods')
@section('heading', 'Payment Methods')

@section('content')
    @php
        $input = 'rounded-xl bg-white border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand';
    @endphp

    <p class="text-sm text-zinc-600 mb-5">These payment options appear in the app's Deposit and Withdraw screens. The account number is where players send their deposit.</p>

    <!-- Add Method Form -->
    <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-5 sm:p-6 mb-6">
        <div class="font-bold text-zinc-900 text-sm mb-3">Add New Method</div>
        <form method="POST" action="{{ route('admin.payment-methods.store') }}" class="flex flex-wrap items-center gap-3">
            @csrf
            <input name="name" placeholder="Name (e.g. bKash)" required class="{{ $input }}">
            <input name="account_number" placeholder="Receiving number" required class="{{ $input }}">
            <label class="flex items-center gap-2 text-sm font-medium text-zinc-700 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="rounded text-brand focus:ring-brand">
                Active
            </label>
            <button class="rounded-xl bg-brand hover:bg-brand-dark text-white px-5 py-2 text-sm font-semibold shadow-sm shadow-orange-500/20 transition-all">Add Method</button>
        </form>
    </div>

    <!-- Existing Methods List -->
    <div class="space-y-3">
        @foreach ($methods as $m)
            <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-4 flex flex-wrap items-center justify-between gap-3">
                <form method="POST" action="{{ route('admin.payment-methods.update', $m) }}" class="flex flex-wrap items-center gap-3">
                    @csrf @method('PUT')
                    <input name="name" value="{{ $m->name }}" required class="{{ $input }}">
                    <input name="account_number" value="{{ $m->account_number }}" required class="{{ $input }}">
                    <label class="flex items-center gap-2 text-sm font-medium text-zinc-700 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" @checked($m->is_active) class="rounded text-brand focus:ring-brand">
                        Active
                    </label>
                    <button class="rounded-xl bg-white border border-zinc-300 text-zinc-700 hover:bg-zinc-50 px-4 py-2 text-sm font-semibold shadow-sm transition-all">Save</button>
                </form>
                <form method="POST" action="{{ route('admin.payment-methods.destroy', $m) }}" onsubmit="return confirm('Delete this method?')" class="shrink-0">
                    @csrf @method('DELETE')
                    <button class="text-xs font-semibold text-rose-600 hover:underline">Delete</button>
                </form>
            </div>
        @endforeach
    </div>
@endsection
