<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TournamentResource;
use App\Http\Resources\TransactionResource;
use App\Models\ModeratorRequest;
use App\Models\PaymentMethod;
use App\Models\Result;
use App\Models\Setting;
use App\Models\Tournament;
use App\Models\User;
use App\Services\OneSignalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ModeratorController extends Controller
{
    /**
     * Player applies to become a moderator.
     * Condition: Must have played at least 5 tournaments (or setting value).
     */
    public function apply(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user->is_moderator, 422, 'You are already a moderator.');

        $min = (int) Setting::get('min_tournaments_for_moderator', 5);
        $played = $user->tournaments_played_count;

        abort_if(
            $played < $min,
            422,
            "You must play at least {$min} tournaments to apply for moderator. You have played {$played} tournament(s)."
        );

        $hasPending = ModeratorRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        abort_if($hasPending, 422, 'You already have a pending moderator application.');

        $data = $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        $modRequest = ModeratorRequest::create([
            'user_id' => $user->id,
            'tournaments_played' => $played,
            'phone' => $user->phone,
            'note' => $data['note'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your moderator application has been submitted to admin for approval.',
            'data' => [
                'id' => $modRequest->id,
                'status' => $modRequest->status,
                'tournaments_played' => $played,
                'min_tournaments_required' => $min,
                'created_at' => $modRequest->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Check player's moderator application status and eligibility.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $min = (int) Setting::get('min_tournaments_for_moderator', 5);
        $played = $user->tournaments_played_count;
        $latest = ModeratorRequest::where('user_id', $user->id)->latest()->first();

        return response()->json([
            'success' => true,
            'data' => [
                'is_moderator' => (bool) $user->is_moderator,
                'tournaments_played' => $played,
                'min_tournaments_required' => $min,
                'can_apply' => ! $user->is_moderator && $played >= $min && (! $latest || $latest->status !== 'pending'),
                'latest_application' => $latest ? [
                    'id' => $latest->id,
                    'status' => $latest->status,
                    'note' => $latest->note,
                    'admin_note' => $latest->admin_note,
                    'created_at' => $latest->created_at->toIso8601String(),
                    'reviewed_at' => $latest->reviewed_at?->toIso8601String(),
                ] : null,
            ],
        ]);
    }

    /**
     * List tournaments arranged by or assigned to this moderator.
     */
    public function tournaments(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Tournament::with(['category'])->withCount('registrations');

        if (! $user->is_admin) {
            $query->where('moderator_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tournaments = $query->latest('start_time')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => TournamentResource::collection($tournaments),
            'pagination' => [
                'current_page' => $tournaments->currentPage(),
                'total_pages' => $tournaments->lastPage(),
                'total_items' => $tournaments->total(),
            ],
        ]);
    }

    /**
     * Moderator arranges/creates a new tournament.
     */
    public function storeTournament(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'title' => 'required|string|max:150',
            'category_id' => 'nullable|exists:categories,id',
            'map' => 'required|string|max:50',
            'type' => 'nullable|string|in:Solo,Duo,Squad,4v4',
            'entry_fee' => 'required|integer|min:0',
            'prize' => 'required|integer|min:0',
            'per_kill' => 'nullable|integer|min:0',
            'slots' => 'required|integer|min:2|max:100',
            'start_time' => 'required|date|after:now',
            'tutorial_video_url' => 'nullable|url',
            'rules_title' => 'nullable|string|max:100',
            'rules' => 'nullable|string',
            'prize_pool_distribution' => 'nullable|array',
        ]);

        $defaultCommission = (int) Setting::get('default_moderator_commission', 50);

        $tournament = Tournament::create([
            ...$data,
            'type' => $data['type'] ?? 'Solo',
            'per_kill' => $data['per_kill'] ?? 0,
            'status' => 'upcoming',
            'moderator_id' => $user->id,
            'moderator_commission' => $defaultCommission,
            'is_commission_paid' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tournament created successfully by moderator.',
            'data' => new TournamentResource($tournament->fresh(['category'])),
        ], 201);
    }

    /**
     * Moderator updates Room ID and Room Password for a tournament.
     * Automatically dispatches push notifications to all registered players.
     */
    public function updateRoomDetails(Request $request, int $id, OneSignalService $oneSignal): JsonResponse
    {
        $user = $request->user();
        $tournament = Tournament::findOrFail($id);

        if (! $user->is_admin && $tournament->moderator_id !== $user->id) {
            abort(403, 'You are not assigned to moderate this tournament.');
        }

        $data = $request->validate([
            'room_id' => 'required|string|max:50',
            'room_password' => 'required|string|max:50',
        ]);

        $tournament->update([
            'room_id' => $data['room_id'],
            'room_password' => $data['room_password'],
            'status' => $tournament->status === 'upcoming' ? 'active' : $tournament->status,
        ]);

        // Send Push Notification to all joined players
        $joinedUserIds = $tournament->registrations()->pluck('user_id')->all();

        if (! empty($joinedUserIds)) {
            $oneSignal->sendRoomDetailsNotification(
                $joinedUserIds,
                $tournament->match_number,
                $tournament->room_id,
                $tournament->room_password,
                $tournament->id
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Room ID & password saved and push notification sent to all joined players.',
            'data' => [
                'tournament_id' => $tournament->id,
                'match_number' => $tournament->match_number,
                'room_id' => $tournament->room_id,
                'room_password' => $tournament->room_password,
                'players_notified' => count($joinedUserIds),
            ],
        ]);
    }

    /**
     * Moderator submits match results for admin approval.
     * Player prizes and moderator commission are disbursed once approved by admin.
     */
    public function storeResults(Request $request, int $id, OneSignalService $oneSignal): JsonResponse
    {
        $user = $request->user();
        $tournament = Tournament::findOrFail($id);

        if (! $user->is_admin && $tournament->moderator_id !== $user->id) {
            abort(403, 'You are not assigned to moderate this tournament.');
        }

        abort_if($tournament->status === 'completed', 422, 'This tournament has already been completed.');

        $request->validate([
            'results' => 'required|array|min:1',
            'results.*.user_id' => 'required|exists:users,id',
            'results.*.position' => 'nullable|integer|min:1',
            'results.*.kills' => 'nullable|integer|min:0',
            'results.*.prize' => 'nullable|integer|min:0',
            'result_note' => 'nullable|string|max:1000',
        ]);

        $joinedIds = $tournament->registrations()->pluck('user_id')->all();

        DB::transaction(function () use ($request, $tournament, $joinedIds) {
            foreach ($request->input('results') as $row) {
                $userId = (int) $row['user_id'];
                if (! in_array($userId, $joinedIds, true)) {
                    continue;
                }

                $kills = (int) ($row['kills'] ?? 0);
                $totalPrize = ($kills * $tournament->per_kill) + (int) ($row['prize'] ?? 0);

                Result::updateOrCreate(
                    ['tournament_id' => $tournament->id, 'user_id' => $userId],
                    [
                        'position' => $row['position'] ?? null,
                        'kills' => $kills,
                        'prize' => $totalPrize,
                    ]
                );
            }

            $tournament->update([
                'status' => 'pending_approval',
                'result_note' => $request->input('result_note'),
                'admin_review_note' => null,
            ]);
        });

        // If called by admin directly, admin can auto-approve
        if ($user->is_admin && $request->boolean('auto_approve', false)) {
            return $this->approveResults($request, $id, $oneSignal);
        }

        return response()->json([
            'success' => true,
            'message' => 'Match results submitted to Admin for approval. Reward money and moderator earning will be added to wallets upon Admin approval.',
            'data' => [
                'tournament_id' => $tournament->id,
                'status' => 'pending_approval',
                'results_count' => count($request->input('results')),
            ],
        ]);
    }

    /**
     * Admin approves match results and disburses reward money and moderator commission.
     */
    public function approveResults(Request $request, int $id, OneSignalService $oneSignal): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->is_admin, 403, 'Only administrators can approve tournament results and send reward money.');

        $tournament = Tournament::findOrFail($id);
        abort_if($tournament->status === 'completed', 422, 'This tournament has already been approved and completed.');

        DB::transaction(function () use ($tournament, $oneSignal) {
            $t = Tournament::lockForUpdate()->findOrFail($tournament->id);
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
                    : (int) ($t->moderator_commission ?: Setting::get('default_moderator_commission', 50));

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

            // Notify all joined players of results and prize distribution
            $oneSignal->sendMatchResultsNotification($joinedIds, $t->match_number, $t->id);
        });

        return response()->json([
            'success' => true,
            'message' => 'Tournament results approved, prizes sent to player wallets, and commission credited to moderator wallet.',
            'data' => [
                'tournament_id' => $tournament->id,
                'status' => 'completed',
            ],
        ]);
    }

    /**
     * Check moderator commission earnings and moderation summary.
     */
    public function earnings(Request $request): JsonResponse
    {
        $user = $request->user();

        $completedTournaments = Tournament::where('moderator_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $recentCommissions = $user->transactions()
            ->where('type', 'commission')
            ->with('tournament')
            ->take(10)
            ->get();

        $totalEarned = (int) $user->transactions()
            ->where('type', 'commission')
            ->where('status', 'approved')
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'moderator_balance' => (int) ($user->moderator_balance ?? 0),
                'total_commission_earned' => $totalEarned,
                'completed_tournaments_count' => $completedTournaments,
                'recent_commissions' => TransactionResource::collection($recentCommissions),
            ],
        ]);
    }

    /**
     * Moderator requests a withdrawal for their commission earnings.
     */
    public function withdraw(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Resolve method alias
        $methodInput = $request->input('method') ?? $request->input('payment_method_id') ?? $request->input('payment_method');
        if (is_numeric($methodInput)) {
            $pm = PaymentMethod::find((int) $methodInput);
            if ($pm) {
                $request->merge(['method' => $pm->name]);
            }
        } elseif (is_string($methodInput) && ! empty($methodInput)) {
            $pm = PaymentMethod::where('name', $methodInput)->first();
            if ($pm) {
                $request->merge(['method' => $pm->name]);
            }
        }

        $minWithdraw = (int) Setting::get('min_withdraw', 50);

        $data = $request->validate([
            'amount' => "required|integer|min:{$minWithdraw}",
            'method' => ['required', Rule::exists('payment_methods', 'name')->where('is_active', true)],
            'account_number' => 'required|string|max:30',
        ]);

        abort_if(
            $user->balance < $data['amount'],
            422,
            "Insufficient balance. Your balance is {$user->balance} BDT."
        );

        $transaction = DB::transaction(function () use ($user, $data) {
            $lockedUser = User::lockForUpdate()->find($user->id);
            $lockedUser->decrement('balance', $data['amount']);

            if ($lockedUser->moderator_balance >= $data['amount']) {
                $lockedUser->decrement('moderator_balance', $data['amount']);
            } else {
                $lockedUser->update(['moderator_balance' => 0]);
            }

            return $lockedUser->transactions()->create([
                'type' => 'withdraw',
                'amount' => $data['amount'],
                'method' => $data['method'],
                'account_number' => $data['account_number'],
                'note' => 'Moderator withdrawal request',
                'status' => 'pending',
            ]);
        });

        return (new TransactionResource($transaction))->response()->setStatusCode(201);
    }
}
