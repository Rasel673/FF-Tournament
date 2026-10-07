@extends('admin.layout')
@section('title', $user->name)
@section('heading', $user->name)

@section('content')
    @php $input = 'rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2'; @endphp

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="space-y-6">
            <div class="rounded-xl bg-zinc-900 border border-zinc-800 p-4 space-y-2 text-sm">
                @if ($user->avatar_url)<img src="{{ $user->avatar_url }}" class="h-20 w-20 rounded-full object-cover" alt="">@endif
                <div><span class="text-zinc-400">UID:</span> {{ $user->uid }}</div>
                <div><span class="text-zinc-400">Phone:</span> {{ $user->phone }}</div>
                <div><span class="text-zinc-400">Balance:</span> <b class="text-white">{{ number_format($user->balance) }} BDT</b></div>
                <div><span class="text-zinc-400">Status:</span> {{ $user->is_blocked ? 'Blocked' : 'Active' }}</div>
                <form method="POST" action="{{ route('admin.users.toggle-block', $user) }}" class="pt-2">
                    @csrf
                    <button class="rounded-lg px-4 py-2 text-white {{ $user->is_blocked ? 'bg-green-700' : 'bg-red-800' }}">
                        {{ $user->is_blocked ? 'Unblock player' : 'Block player' }}
                    </button>
                </form>
            </div>

            <form method="POST" action="{{ route('admin.users.adjust-balance', $user) }}" class="rounded-xl bg-zinc-900 border border-zinc-800 p-4 space-y-3 text-sm">
                @csrf
                <div class="font-medium text-white">Adjust balance</div>
                <select name="type" class="{{ $input }} w-full">
                    <option value="bonus">Add bonus (+)</option>
                    <option value="penalty">Deduct (−)</option>
                </select>
                <input type="number" min="1" name="amount" placeholder="Amount (BDT)" required class="{{ $input }} w-full">
                <input name="note" placeholder="Note (optional)" class="{{ $input }} w-full">
                <button class="rounded-lg bg-orange-600 hover:bg-orange-700 text-white px-4 py-2">Apply</button>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-zinc-900 border border-zinc-800 overflow-x-auto">
                <div class="p-3 font-medium text-white">Recent transactions</div>
                <table class="w-full text-sm">
                    <tbody>
                    @forelse ($transactions as $tx)
                        <tr class="border-t border-zinc-800">
                            <td class="p-3">{{ $tx->title }}</td>
                            <td class="p-3 {{ $tx->signed_amount >= 0 ? 'text-green-400' : 'text-red-400' }}">{{ $tx->signed_amount > 0 ? '+' : '' }}{{ $tx->signed_amount }}</td>
                            <td class="p-3">{{ ucfirst($tx->status) }}</td>
                            <td class="p-3 text-zinc-400">{{ $tx->created_at->format('d M, g:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td class="p-6 text-center text-zinc-500">No transactions.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="rounded-xl bg-zinc-900 border border-zinc-800 overflow-x-auto">
                <div class="p-3 font-medium text-white">Joined tournaments</div>
                <table class="w-full text-sm">
                    <tbody>
                    @forelse ($registrations as $reg)
                        <tr class="border-t border-zinc-800">
                            <td class="p-3">{{ $reg->tournament->title }}</td>
                            <td class="p-3">{{ ucfirst($reg->tournament->status) }}</td>
                            <td class="p-3 text-zinc-400">{{ $reg->created_at->format('d M, g:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td class="p-6 text-center text-zinc-500">No tournaments joined.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
