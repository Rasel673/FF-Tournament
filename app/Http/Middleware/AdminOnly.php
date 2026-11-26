<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_admin) {
            if ($user && $user->is_moderator) {
                return redirect()->route('admin.tournaments.index')
                    ->with('error', 'Only super administrators have access to that section.');
            }

            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
