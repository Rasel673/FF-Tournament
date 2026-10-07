<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('is_admin', false)
            ->when($request->q, fn ($q, $term) => $q->where(function ($w) use ($term) {
                $w->where('name', 'like', "%$term%")
                    ->orWhere('phone', 'like', "%$term%")
                    ->orWhere('uid', 'like', "%$term%");
            }))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        return view('admin.users.show', [
            'user' => $user,
            'transactions' => $user->transactions()->with('tournament')->limit(30)->get(),
            'registrations' => $user->registrations()->with('tournament')->latest()->limit(20)->get(),
        ]);
    }

    public function toggleBlock(User $user)
    {
        $user->update(['is_blocked' => ! $user->is_blocked]);

        if ($user->is_blocked) {
            $user->tokens()->delete(); // log the player out of the app
        }

        return back()->with('success', $user->is_blocked ? 'User blocked.' : 'User unblocked.');
    }

    /** Add (bonus) or remove (penalty) wallet balance manually. */
    public function adjustBalance(Request $request, User $user)
    {
        $data = $request->validate([
            'type' => 'required|in:bonus,penalty',
            'amount' => 'required|integer|min:1',
            'note' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($user, $data) {
            $locked = User::lockForUpdate()->findOrFail($user->id);

            if ($data['type'] === 'penalty') {
                abort_if($locked->balance < $data['amount'], 422, 'Balance is lower than the amount to deduct.');
                $locked->decrement('balance', $data['amount']);
            } else {
                $locked->increment('balance', $data['amount']);
            }

            $locked->transactions()->create([
                'type' => $data['type'],
                'amount' => $data['amount'],
                'status' => 'approved',
                'note' => $data['note'] ?? null,
            ]);
        });

        return back()->with('success', 'Balance updated.');
    }
}
