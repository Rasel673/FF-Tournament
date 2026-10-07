@extends('admin.layout')
@section('title', 'Payment Methods')
@section('heading', 'Payment Methods')

@section('content')
    @php $input = 'rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm'; @endphp

    <p class="text-sm text-zinc-400 mb-4">These appear in the app's Deposit and Withdraw screens. The account number is where players send their deposit.</p>

    <form method="POST" action="{{ route('admin.payment-methods.store') }}" class="flex flex-wrap gap-2 mb-6">
        @csrf
        <input name="name" placeholder="Name (e.g. bKash)" required class="{{ $input }}">
        <input name="account_number" placeholder="Receiving number" required class="{{ $input }}">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active</label>
        <button class="rounded-lg bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 text-sm">Add</button>
    </form>

    <div class="space-y-2">
        @foreach ($methods as $m)
            <div class="rounded-xl bg-zinc-900 border border-zinc-800 p-3 flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('admin.payment-methods.update', $m) }}" class="flex flex-wrap items-center gap-2">
                    @csrf @method('PUT')
                    <input name="name" value="{{ $m->name }}" required class="{{ $input }}">
                    <input name="account_number" value="{{ $m->account_number }}" required class="{{ $input }}">
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($m->is_active)> Active</label>
                    <button class="rounded-lg border border-zinc-600 px-3 py-2 text-sm hover:bg-zinc-800">Save</button>
                </form>
                <form method="POST" action="{{ route('admin.payment-methods.destroy', $m) }}" onsubmit="return confirm('Delete this method?')">
                    @csrf @method('DELETE')
                    <button class="text-sm text-red-400 hover:underline">Delete</button>
                </form>
            </div>
        @endforeach
    </div>
@endsection
