<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\TournamentResource;
use App\Http\Resources\UserResource;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Tournament;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /** Home screen: profile header (name, UID, balance) + active/upcoming tournaments. */
    public function index(Request $request)
    {
        $user = $request->user();

        $tournaments = Tournament::withUserState($user->id)
            ->with('category')
            ->whereIn('status', ['active', 'upcoming'])
            ->orderBy('start_time')
            ->get();

        $banners = Banner::where('is_active', true)
            ->orderBy('order')
            ->orderByDesc('id')
            ->get();

        $categories = Category::where('is_active', true)
            ->withCount(['tournaments' => fn ($q) => $q->whereIn('status', ['active', 'upcoming'])])
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => [
                'user' => new UserResource($user),
                'banners' => BannerResource::collection($banners),
                'support' => [
                    'title' => 'Need any Help?',
                    'subtitle' => 'Customer Support',
                    'button_text' => 'Contact >',
                    'phone' => Setting::get('support_phone'),
                    'whatsapp' => Setting::get('support_whatsapp'),
                    'telegram' => Setting::get('support_telegram'),
                    'email' => Setting::get('support_email'),
                ],
                'featured_game' => [
                    'title' => 'Free Fire Matches',
                    'subtitle' => 'JOIN THE BATTLE',
                ],
                'categories' => CategoryResource::collection($categories),
                'tournaments' => TournamentResource::collection($tournaments),
            ],
        ]);
    }
}
