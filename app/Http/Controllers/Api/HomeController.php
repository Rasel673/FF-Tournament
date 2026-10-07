<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TournamentResource;
use App\Http\Resources\UserResource;
use App\Models\Tournament;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /** Home screen: profile header (name, UID, balance) + active/upcoming tournaments. */
    public function index(Request $request)
    {
        $user = $request->user();

        $tournaments = Tournament::withUserState($user->id)
            ->whereIn('status', ['active', 'upcoming'])
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'data' => [
                'user' => new UserResource($user),
                'tournaments' => TournamentResource::collection($tournaments),
            ],
        ]);
    }
}
