<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        $query = Notification::query();

        if ($user) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                    ->orWhere('user_id', $user->id);
            });
        } else {
            $query->whereNull('user_id');
        }

        return response()->json([
            'success' => true,
            'data' => NotificationResource::collection(
                $query->latest()->get()
            ),
        ]);
    }
}
