<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Tournament;
use App\Models\User;
use App\Services\OneSignalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = Notification::with('user')
            ->latest()
            ->paginate(15);

        $users = User::where('is_admin', false)
            ->orderBy('name')
            ->take(300)
            ->get(['id', 'name', 'phone', 'uid']);

        $tournaments = Tournament::latest()
            ->take(30)
            ->get(['id', 'title', 'match_number', 'status']);

        $preselectedUserId = $request->query('user_id');

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'users' => $users,
            'tournaments' => $tournaments,
            'preselectedUserId' => $preselectedUserId,
        ]);
    }

    public function store(Request $request, OneSignalService $oneSignal): RedirectResponse
    {
        $data = $request->validate([
            'target' => 'required|in:all,specific',
            'user_id' => 'required_if:target,specific|nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'big_picture_file' => 'nullable|image|max:5120',
            'big_picture_url' => 'nullable|url|max:500',
            'large_icon_file' => 'nullable|image|max:2048',
            'large_icon_url' => 'nullable|url|max:500',
            'data_type' => 'nullable|string|in:none,tournament,wallet,profile',
            'tournament_id' => 'nullable|required_if:data_type,tournament|exists:tournaments,id',
        ]);

        // Resolve big picture (banner)
        $bigPicture = null;
        if ($request->hasFile('big_picture_file')) {
            $path = $request->file('big_picture_file')->store('notifications', 'public');
            $bigPicture = asset('storage/' . $path);
        } elseif (!empty($data['big_picture_url'])) {
            $bigPicture = $data['big_picture_url'];
        }

        // Resolve large icon (avatar/badge)
        $largeIcon = null;
        if ($request->hasFile('large_icon_file')) {
            $path = $request->file('large_icon_file')->store('notifications', 'public');
            $largeIcon = asset('storage/' . $path);
        } elseif (!empty($data['large_icon_url'])) {
            $largeIcon = $data['large_icon_url'];
        }

        // Resolve deep link data
        $customData = null;
        if (!empty($data['data_type']) && $data['data_type'] !== 'none') {
            if ($data['data_type'] === 'tournament' && !empty($data['tournament_id'])) {
                $customData = [
                    'type' => 'tournament',
                    'tournament_id' => (string) $data['tournament_id'],
                ];
            } else {
                $customData = [
                    'type' => $data['data_type'],
                ];
            }
        }

        // Send via OneSignal
        $targetUser = null;
        if ($data['target'] === 'specific') {
            $targetUser = User::findOrFail($data['user_id']);
            $res = $oneSignal->sendToUser(
                $targetUser->id,
                $data['title'],
                $data['message'],
                $bigPicture,
                $largeIcon,
                $customData
            );
        } else {
            $res = $oneSignal->sendToAll(
                $data['title'],
                $data['message'],
                $bigPicture,
                $largeIcon,
                $customData
            );
        }

        // Record in database
        Notification::create([
            'user_id' => $targetUser?->id,
            'target' => $data['target'],
            'external_id' => $targetUser ? (string) $targetUser->id : null,
            'title' => $data['title'],
            'description' => $data['message'],
            'large_image' => $bigPicture,
            'small_icon' => $largeIcon,
            'data' => $customData,
            'onesignal_id' => $res['id'] ?? null,
            'recipients_count' => $res['recipients'] ?? 0,
            'status' => $res['success'] ? 'sent' : 'failed',
            'error_message' => $res['error'] ?? null,
        ]);

        if ($res['success']) {
            $targetDesc = $data['target'] === 'all'
                ? 'All Users'
                : ($targetUser ? $targetUser->name . ' (UID: ' . $targetUser->uid . ')' : 'Specific User');
            return back()->with('success', "Notification sent successfully to {$targetDesc}! (OneSignal ID: {$res['id']})");
        }

        return back()->with('error', "Notification saved, but OneSignal reported an error: " . ($res['error'] ?? 'Unknown error'));
    }

    public function destroy(Notification $notification): RedirectResponse
    {
        $notification->delete();
        return back()->with('success', 'Notification removed from history.');
    }
}

