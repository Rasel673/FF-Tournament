<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModeratorRequest;
use App\Models\User;
use App\Services\OneSignalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModeratorController extends Controller
{
    public function index(): View
    {
        $moderators = User::where('is_moderator', true)
            ->withCount('moderatedTournaments')
            ->orderBy('name')
            ->paginate(15, ['*'], 'moderators_page');

        $requests = ModeratorRequest::with('user')
            ->latest()
            ->paginate(15, ['*'], 'requests_page');

        $availableUsers = User::where('is_admin', false)
            ->where('is_moderator', false)
            ->orderBy('name')
            ->take(150)
            ->get(['id', 'name', 'phone', 'uid']);

        return view('admin.moderators.index', [
            'moderators' => $moderators,
            'requests' => $requests,
            'availableUsers' => $availableUsers,
        ]);
    }

    /**
     * Admin creates a brand new Moderator user account.
     */
    public function createAccount(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6',
            'email' => 'nullable|email|max:150|unique:users,email',
            'moderator_commission_rate' => 'nullable|integer|min:0',
        ]);

        $defaultCommission = (int) \App\Models\Setting::get('default_moderator_commission', 50);

        $user = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
            'is_moderator' => true,
            'is_admin' => false,
            'is_blocked' => false,
            'moderator_commission_rate' => $data['moderator_commission_rate'] ?? $defaultCommission,
        ]);

        return back()->with('success', "Moderator account '{$user->name}' created successfully! Registered phone for login: {$user->phone}");
    }

    /**
     * Admin directly adds/promotes any user to Moderator without restrictions.
     */
    public function store(Request $request, OneSignalService $oneSignal): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'moderator_commission_rate' => 'nullable|integer|min:0',
        ]);

        $user = User::findOrFail($data['user_id']);
        $user->update([
            'is_moderator' => true,
            'moderator_commission_rate' => $data['moderator_commission_rate'] ?? null,
        ]);

        // If user had a pending request, mark it approved
        ModeratorRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'admin_note' => 'Directly added by admin.',
            ]);

        // Send Push Notification to the user
        $oneSignal->sendToUser(
            $user->id,
            'Congratulations! You are now a Moderator',
            'Admin has approved you as a Moderator. You can now arrange tournaments and earn commissions!'
        );

        return back()->with('success', "{$user->name} has been appointed as a Moderator.");
    }

    /**
     * Admin approves a player's moderator request.
     */
    public function approveRequest(Request $request, ModeratorRequest $moderatorRequest, OneSignalService $oneSignal): RedirectResponse
    {
        $moderatorRequest->update([
            'status' => 'approved',
            'admin_note' => $request->input('admin_note', 'Approved by admin.'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $user = $moderatorRequest->user;
        $user->update(['is_moderator' => true]);

        // Send Push Notification
        $oneSignal->sendToUser(
            $user->id,
            'Moderator Request Approved!',
            'Your application to become a tournament moderator has been approved! You can now arrange matches and earn commissions.'
        );

        return back()->with('success', "Moderator application for {$user->name} has been approved.");
    }

    /**
     * Admin rejects a player's moderator request.
     */
    public function rejectRequest(Request $request, ModeratorRequest $moderatorRequest, OneSignalService $oneSignal): RedirectResponse
    {
        $moderatorRequest->update([
            'status' => 'rejected',
            'admin_note' => $request->input('admin_note', 'Rejected by admin.'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // Send Push Notification
        $oneSignal->sendToUser(
            $moderatorRequest->user_id,
            'Moderator Application Update',
            'Your moderator application was not approved at this time: ' . ($request->input('admin_note') ?: 'Please play more matches and try again.')
        );

        return back()->with('success', "Moderator application rejected.");
    }

    /**
     * Admin revokes moderator status.
     */
    public function revoke(User $user): RedirectResponse
    {
        $user->update(['is_moderator' => false]);

        return back()->with('success', "Moderator privileges revoked for {$user->name}.");
    }

    /**
     * Admin updates custom commission rate for a moderator.
     */
    public function updateCommission(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'moderator_commission_rate' => 'nullable|integer|min:0',
        ]);

        $user->update([
            'moderator_commission_rate' => $data['moderator_commission_rate'] ?? null,
        ]);

        return back()->with('success', "Commission rate updated for {$user->name}.");
    }
}
