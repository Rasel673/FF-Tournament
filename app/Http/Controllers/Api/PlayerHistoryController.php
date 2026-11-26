<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TournamentResource;
use App\Models\Tournament;
use Illuminate\Http\Request;

class PlayerHistoryController extends Controller
{
    public function index(Request $request)
    {
        $history = Tournament::query()
            ->withUserState($request->user()->id)
            ->with(['category', 'results.user'])
            ->whereHas('registrations', fn ($query) => $query->where('user_id', $request->user()->id))
            ->orderByDesc('start_time')
            ->paginate(20);

        return TournamentResource::collection($history);
    }
}
