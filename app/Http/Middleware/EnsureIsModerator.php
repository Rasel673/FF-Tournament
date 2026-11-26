<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsModerator
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->is_moderator && ! $user->is_admin)) {
            abort(403, 'Unauthorized. Moderator access required.');
        }

        return $next($request);
    }
}
