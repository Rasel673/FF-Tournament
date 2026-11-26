<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Result;
use App\Models\Tournament;
use App\Models\User;
use App\Services\OneSignalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TournamentController extends Controller
{
    private function authorizeTournamentAccess(Tournament $tournament): void
    {
        $user = auth()->user();
        if ($user && ! $user->is_admin && $user->is_moderator) {
            if ($tournament->moderator_id !== $user->id) {
                abort(403, 'Unauthorized. You are not assigned to moderate this tournament.');
            }
        }
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $tournaments = Tournament::with(['category', 'moderator'])->withCount('registrations')
            ->when($user && ! $user->is_admin && $user->is_moderator, fn ($q) => $q->where('moderator_id', $user->id))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->category_id, fn ($q, $cat) => $q->where('category_id', $cat))
            ->latest('start_time')->paginate(15)->withQueryString();

        $categories = Category::orderBy('title')->get();

        return view('admin.tournaments.index', compact('tournaments', 'categories'));
    }

    public function create()
    {
        $user = auth()->user();
        $categories = Category::where('is_active', true)->orderBy('order')->orderBy('title')->get();
        $moderators = ($user && $user->is_admin)
            ? User::where('is_moderator', true)->orWhere('is_admin', true)->orderBy('name')->get()
            : collect([$user]);

        $defaultCommission = (int) ($user?->moderator_commission_rate ?? \App\Models\Setting::get('default_moderator_commission', 50));

        return view('admin.tournaments.form', [
            'tournament' => new Tournament([
                'slots' => 50,
                'status' => 'upcoming',
                'moderator_id' => ($user && ! $user->is_admin) ? $user->id : null,
                'moderator_commission' => $defaultCommission,
            ]),
            'categories' => $categories,
            'moderators' => $moderators,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $data = $this->validated($request);

        if ($user && ! $user->is_admin && $user->is_moderator) {
            $data['moderator_id'] = $user->id;
            $data['moderator_commission'] = (int) ($user->moderator_commission_rate ?? \App\Models\Setting::get('default_moderator_commission', 50));
        }

        Tournament::create($data);

        return redirect()->route('admin.tournaments.index')->with('success', 'Tournament created.');
    }

    public function edit(Tournament $tournament)
    {
        $this->authorizeTournamentAccess($tournament);

        $user = auth()->user();
        $categories = Category::where('is_active', true)
            ->orWhere('id', $tournament->category_id)
            ->orderBy('order')
            ->orderBy('title')
            ->get();
        $moderators = ($user && $user->is_admin)
            ? User::where('is_moderator', true)->orWhere('is_admin', true)->orderBy('name')->get()
            : collect([$user]);

        return view('admin.tournaments.form', compact('tournament', 'categories', 'moderators'));
    }

    public function update(Request $request, Tournament $tournament, OneSignalService $oneSignal)
    {
        $this->authorizeTournamentAccess($tournament);

        $oldRoomId = $tournament->room_id;
        $oldRoomPass = $tournament->room_password;

        $tournament->update($this->validated($request));

        // If Room details were newly entered or changed, notify players
        if (! empty($tournament->room_id) && ! empty($tournament->room_password)) {
            if ($tournament->room_id !== $oldRoomId || $tournament->room_password !== $oldRoomPass) {
                $joinedIds = $tournament->registrations()->pluck('user_id')->all();
                if (! empty($joinedIds)) {
                    $oneSignal->sendRoomDetailsNotification(
                        $joinedIds,
                        $tournament->match_number,
                        $tournament->room_id,
                        $tournament->room_password,
                        $tournament->id
                    );
                }
            }
        }

        return redirect()->route('admin.tournaments.index')->with('success', 'Tournament updated.');
    }

    public function destroy(Tournament $tournament)
    {
        $this->authorizeTournamentAccess($tournament);

        if ($tournament->registrations()->exists()) {
            return back()->with('error', 'Players already joined this tournament, so it cannot be deleted.');
        }

        $tournament->delete();

        return back()->with('success', 'Tournament deleted.');
    }

    /** Enter winners/kills for every joined player. */
    public function results(Tournament $tournament)
    {
        $this->authorizeTournamentAccess($tournament);

        $results = $tournament->results()->with('user')->get();

        return view('admin.tournaments.results', [
            'tournament' => $tournament,
            'registrations' => $tournament->registrations()->with('user')->get(),
            'results' => $results,
            'resultsByUser' => $results->keyBy('user_id'),
        ]);
    }

    public function storeResults(Request $request, Tournament $tournament, OneSignalService $oneSignal)
    {
        $this->authorizeTournamentAccess($tournament);

        $request->validate([
            'results' => 'nullable|array',
            'results.*.position' => 'nullable|integer|min:1',
            'results.*.kills' => 'nullable|integer|min:0',
            'results.*.prize' => 'nullable|integer|min:0',
            'result_note' => 'nullable|string|max:1000',
            'proof_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $user = auth()->user();
        $isModerator = $user && $user->is_moderator && ! $user->is_admin;
        $joinedIds = $tournament->registrations()->pluck('user_id')->all();

        DB::transaction(function () use ($request, $tournament, $joinedIds) {
            // Save or stage player positions, kills, and calculated prizes
            foreach ($request->input('results', []) as $userId => $row) {
                if (! in_array((int) $userId, $joinedIds, true)) {
                    continue;
                }

                $kills = (int) ($row['kills'] ?? 0);
                $positionPrize = (int) ($row['prize'] ?? 0);
                $totalPrize = $kills * $tournament->per_kill + $positionPrize;

                Result::updateOrCreate(
                    ['tournament_id' => $tournament->id, 'user_id' => $userId],
                    [
                        'position' => $row['position'] ?: null,
                        'kills' => $kills,
                        'prize' => $totalPrize,
                    ]
                );
            }

            $update = [
                'result_note' => $request->input('result_note'),
            ];

            if ($request->hasFile('proof_image')) {
                if ($tournament->proof_image) {
                    Storage::disk('public')->delete($tournament->proof_image);
                    @unlink(public_path('storage/'.$tournament->proof_image));
                }
                $update['proof_image'] = $request->file('proof_image')->store('proofs', 'public');
            }

            $tournament->update($update);
        });

        // If submitted by moderator, tournament moves to pending_approval and awaits Admin review
        if ($isModerator) {
            $tournament->update([
                'status' => 'pending_approval',
                'admin_review_note' => null,
            ]);

            return redirect()->route('admin.tournaments.index')
                ->with('success', 'Match results submitted to Admin for approval. Reward money and your commission will be disbursed once approved.');
        }

        // If submitted by Admin directly:
        if ($request->input('action') === 'submit_for_review') {
            $tournament->update(['status' => 'pending_approval']);
            return redirect()->route('admin.tournaments.index')
                ->with('success', 'Results saved and marked as awaiting approval.');
        }

        // Admin publishes and disburses immediately
        $this->disburseTournamentRewards($tournament, $oneSignal);

        return redirect()->route('admin.tournaments.index')
            ->with('success', 'Results published and rewards disbursed to players, moderator, and admin.');
    }

    /**
     * Admin approves results and disburses rewards (70% players, 10% moderator, 20% admin).
     */
    public function approveResults(Tournament $tournament, OneSignalService $oneSignal)
    {
        abort_unless(auth()->user()?->is_admin, 403, 'Only administrators can approve tournament results and send rewards.');
        abort_if($tournament->status === 'completed', 422, 'This tournament results have already been approved and disbursed.');

        $this->disburseTournamentRewards($tournament, $oneSignal);

        return redirect()->route('admin.tournaments.index')
            ->with('success', "Match #{$tournament->match_number} results approved! Rewards disbursed to players, moderator earning added to wallet, and admin income recorded.");
    }

    /**
     * Admin rejects results and returns to moderator with review notes for revision.
     */
    public function rejectResults(Request $request, Tournament $tournament, OneSignalService $oneSignal)
    {
        abort_unless(auth()->user()?->is_admin, 403, 'Only administrators can reject tournament results.');
        abort_if($tournament->status === 'completed', 422, 'Completed tournament cannot be rejected.');

        $request->validate([
            'admin_review_note' => 'required|string|max:1000',
        ]);

        $tournament->update([
            'status' => 'active',
            'admin_review_note' => $request->input('admin_review_note'),
        ]);

        if ($tournament->moderator_id) {
            $oneSignal->sendToUser(
                $tournament->moderator_id,
                "Match Results Revision Needed ({$tournament->match_number})",
                "Admin feedback: " . $request->input('admin_review_note')
            );
        }

        return redirect()->route('admin.tournaments.index')
            ->with('success', "Match #{$tournament->match_number} results rejected and returned to moderator with your review feedback.");
    }

    /**
     * Disburse 70% player prizes, 10% moderator earning, and 20% admin platform revenue.
     */
    public function disburseTournamentRewards(Tournament $tournament, OneSignalService $oneSignal): void
    {
        DB::transaction(function () use ($tournament, $oneSignal) {
            $t = Tournament::lockForUpdate()->findOrFail($tournament->id);
            if ($t->status === 'completed') {
                return;
            }

            $joinedIds = $t->registrations()->pluck('user_id')->all();
            $joinedCount = count($joinedIds);
            $totalEntryCollection = $joinedCount * (int) $t->entry_fee;

            // 1. Distribute 70% Player Rewards (prizes & kills)
            $results = $t->results()->get();
            foreach ($results as $result) {
                if (! in_array((int) $result->user_id, $joinedIds, true)) {
                    continue;
                }

                if ($result->prize > 0) {
                    $player = User::lockForUpdate()->findOrFail($result->user_id);
                    $player->increment('balance', $result->prize);
                    $player->transactions()->create([
                        'type' => 'prize',
                        'amount' => $result->prize,
                        'tournament_id' => $t->id,
                        'status' => 'approved',
                    ]);
                }
            }

            // 2. 10% Moderator Earning
            if (! $t->is_commission_paid && $t->moderator_id) {
                $commission = $totalEntryCollection > 0
                    ? max((int) round($totalEntryCollection * 0.10), (int) ($t->moderator_commission ?: 0))
                    : (int) ($t->moderator_commission ?: 50);

                if ($commission > 0) {
                    $moderator = User::lockForUpdate()->find($t->moderator_id);
                    if ($moderator) {
                        $moderator->increment('moderator_balance', $commission);
                        $moderator->increment('balance', $commission);
                        $moderator->transactions()->create([
                            'type' => 'commission',
                            'amount' => $commission,
                            'tournament_id' => $t->id,
                            'status' => 'approved',
                            'note' => 'Tournament moderator earning',
                        ]);

                        $oneSignal->sendCommissionNotification(
                            $moderator->id,
                            $t->match_number,
                            $commission,
                            $t->id
                        );
                    }
                }

                $t->update([
                    'is_commission_paid' => true,
                    'moderator_commission' => $commission,
                ]);
            }

            // 3. 20% Admin Income (or 30% if no moderator assigned)
            if ($totalEntryCollection > 0) {
                $adminPercentage = $t->moderator_id ? 0.20 : 0.30;
                $adminIncome = (int) round($totalEntryCollection * $adminPercentage);

                if ($adminIncome > 0) {
                    $admin = User::where('is_admin', true)->lockForUpdate()->first();
                    if ($admin) {
                        $admin->increment('balance', $adminIncome);
                        $admin->transactions()->create([
                            'type' => 'admin_income',
                            'amount' => $adminIncome,
                            'tournament_id' => $t->id,
                            'status' => 'approved',
                            'note' => 'Tournament entry fee revenue (' . (int) ($adminPercentage * 100) . '% admin share)',
                        ]);
                    }
                }
            }

            $t->update([
                'status' => 'completed',
                'admin_review_note' => null,
            ]);

            // Notify players of results and prizes
            if (! empty($joinedIds)) {
                $oneSignal->sendMatchResultsNotification($joinedIds, $t->match_number, $t->id);
            }
        });
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'match_number' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:categories,id',
            'moderator_id' => 'nullable|exists:users,id',
            'moderator_commission' => 'nullable|integer|min:0',
            'title' => 'required|string|max:100',
            'map' => 'required|string|max:50',
            'type' => 'required|string|in:Solo,Duo,Squad,4v4',
            'entry_fee' => 'required|integer|min:0',
            'prize' => 'required|integer|min:0',
            'per_kill' => 'required|integer|min:0',
            'slots' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'open_time' => 'nullable|date',
            'status' => 'required|in:upcoming,active,pending_approval,completed',
            'room_id' => 'nullable|string|max:50',
            'room_password' => 'nullable|string|max:50',
            'tutorial_video_url' => 'nullable|url|max:255',
            'rules_title' => 'nullable|string|max:100',
            'rules' => 'nullable|string',
        ]);

        $entryFee = (int) $data['entry_fee'];
        $slots = (int) $data['slots'];
        $totalPool = $entryFee * $slots;

        // Auto-calculate 70% Player Reward and 10% Moderator Commission if not explicitly set
        if ($totalPool > 0 && empty($data['prize'])) {
            $data['prize'] = (int) round($totalPool * 0.70);
        }
        if ($totalPool > 0 && empty($data['moderator_commission'])) {
            $data['moderator_commission'] = (int) round($totalPool * 0.10);
        }

        // Process prize pool distribution if provided
        if ($request->has('prize_positions') && is_array($request->prize_positions)) {
            $prizes = [];
            foreach ($request->prize_positions as $i => $pos) {
                $pos = trim($pos);
                $amt = (int) ($request->prize_amounts[$i] ?? 0);
                if (!empty($pos) || $amt > 0) {
                    $prizes[] = [
                        'position' => $pos ?: ('Position ' . ($i + 1)),
                        'prize' => $amt,
                        'prize_formatted' => $amt . ' Taka',
                    ];
                }
            }
            if (!empty($prizes)) {
                $data['prize_pool_distribution'] = $prizes;
            }
        }

        return $data;
    }
}
