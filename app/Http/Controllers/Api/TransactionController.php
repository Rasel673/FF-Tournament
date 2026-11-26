<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /** Transaction History: GET /transactions?type=deposit|withdraw|tournament (omit for "All"). */
    public function index(Request $request)
    {
        $query = $request->user()->transactions()->with('tournament');

        match ($request->type) {
            'deposit' => $query->where('type', 'deposit'),
            'withdraw' => $query->where('type', 'withdraw'),
            'tournament' => $query->whereIn('type', ['tournament_entry', 'prize']),
            default => null,
        };

        return TransactionResource::collection($query->paginate(20));
    }
}
