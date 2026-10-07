<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TournamentResource;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TournamentController extends Controller
{
    /** Match tab: GET /tournaments?status=active|upcoming|completed (omit for "All"). */
    public function index(Request $request)
    {
        $query = Tournament::withUserState($request->user()->id)->orderByDesc('start_time');

        if (in_array($request->status, ['active', 'upcoming', 'completed'], true)) {
            $query->where('status', $request->status);
        }

        return TournamentResource::collection($query->paginate(20));
    }

    public function show(Request $request, int $id)
    {
        $tournament = Tournament::withUserState($request->user()->id)
            ->with('results.user')
            ->findOrFail($id);

        return new TournamentResource($tournament);
    }

    public function join(Request $request, int $id)
    {
        $userId = $request->user()->id;

        DB::transaction(function () use ($userId, $id) {
            // Lock rows so two players can't take the last slot / spend the same balance twice.
            $tournament = Tournament::lockForUpdate()->findOrFail($id);
            $user = User::lockForUpdate()->findOrFail($userId);

            abort_if($tournament->status !== 'active', 422, 'This tournament is not open for joining.');
            abort_if($tournament->registrations()->where('user_id', $userId)->exists(), 422, 'You already joined this tournament.');
            abort_if($tournament->registrations()->count() >= $tournament->slots, 422, 'No slots left.');
            abort_if($user->balance < $tournament->entry_fee, 422, 'Insufficient balance. Please deposit first.');

            $user->decrement('balance', $tournament->entry_fee);
            $tournament->registrations()->create(['user_id' => $userId]);
            $user->transactions()->create([
                'type' => 'tournament_entry',
                'amount' => $tournament->entry_fee,
                'tournament_id' => $tournament->id,
                'status' => 'approved',
            ]);
        });

        $tournament = Tournament::withUserState($userId)->with('results.user')->findOrFail($id);

        return new TournamentResource($tournament);
    }
}
