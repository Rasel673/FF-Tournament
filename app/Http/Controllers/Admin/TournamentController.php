<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TournamentController extends Controller
{
    public function index(Request $request)
    {
        $tournaments = Tournament::withCount('registrations')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest('start_time')->paginate(15)->withQueryString();

        return view('admin.tournaments.index', compact('tournaments'));
    }

    public function create()
    {
        return view('admin.tournaments.form', ['tournament' => new Tournament(['slots' => 50, 'status' => 'upcoming'])]);
    }

    public function store(Request $request)
    {
        Tournament::create($this->validated($request));

        return redirect()->route('admin.tournaments.index')->with('success', 'Tournament created.');
    }

    public function edit(Tournament $tournament)
    {
        return view('admin.tournaments.form', compact('tournament'));
    }

    public function update(Request $request, Tournament $tournament)
    {
        $tournament->update($this->validated($request));

        return redirect()->route('admin.tournaments.index')->with('success', 'Tournament updated.');
    }

    public function destroy(Tournament $tournament)
    {
        if ($tournament->registrations()->exists()) {
            return back()->with('error', 'Players already joined this tournament, so it cannot be deleted.');
        }

        $tournament->delete();

        return back()->with('success', 'Tournament deleted.');
    }

    /** Enter winners/kills for every joined player. */
    public function results(Tournament $tournament)
    {
        return view('admin.tournaments.results', [
            'tournament' => $tournament,
            'registrations' => $tournament->registrations()->with('user')->get(),
            'results' => $tournament->results()->with('user')->get(),
        ]);
    }

    public function storeResults(Request $request, Tournament $tournament)
    {
        $request->validate([
            'results' => 'nullable|array',
            'results.*.position' => 'nullable|integer|min:1',
            'results.*.kills' => 'nullable|integer|min:0',
            'results.*.prize' => 'nullable|integer|min:0',
            'result_note' => 'nullable|string|max:1000',
            'proof_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        DB::transaction(function () use ($request, $tournament) {
            $wasCompleted = $tournament->status === 'completed';

            // Prizes are paid only once, the first time results are published.
            if (! $wasCompleted) {
                $joinedIds = $tournament->registrations()->pluck('user_id')->all();

                foreach ($request->input('results', []) as $userId => $row) {
                    if (! in_array((int) $userId, $joinedIds, true)) {
                        continue;
                    }

                    $kills = (int) ($row['kills'] ?? 0);
                    $total = $kills * $tournament->per_kill + (int) ($row['prize'] ?? 0);

                    Result::updateOrCreate(
                        ['tournament_id' => $tournament->id, 'user_id' => $userId],
                        ['position' => $row['position'] ?: null, 'kills' => $kills, 'prize' => $total]
                    );

                    if ($total > 0) {
                        $player = User::lockForUpdate()->findOrFail($userId);
                        $player->increment('balance', $total);
                        $player->transactions()->create([
                            'type' => 'prize',
                            'amount' => $total,
                            'tournament_id' => $tournament->id,
                            'status' => 'approved',
                        ]);
                    }
                }
            }

            $update = ['status' => 'completed', 'result_note' => $request->input('result_note')];

            if ($request->hasFile('proof_image')) {
                if ($tournament->proof_image) {
                    Storage::disk('public')->delete($tournament->proof_image);
                }
                $update['proof_image'] = $request->file('proof_image')->store('proofs', 'public');
            }

            $tournament->update($update);
        });

        return redirect()->route('admin.tournaments.index')->with('success', 'Results saved.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:100',
            'map' => 'required|string|max:50',
            'entry_fee' => 'required|integer|min:0',
            'prize' => 'required|integer|min:0',
            'per_kill' => 'required|integer|min:0',
            'slots' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'open_time' => 'nullable|date',
            'status' => 'required|in:upcoming,active,completed',
            'room_id' => 'nullable|string|max:50',
            'room_password' => 'nullable|string|max:50',
        ]);
    }
}
