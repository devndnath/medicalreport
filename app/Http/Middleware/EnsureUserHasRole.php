<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role) {
            abort(403, 'You do not have permission to access this section.');
        }

        // Super Admin can see everything any role group can see, in whichever
        // single factory they are currently viewing (set by SetCurrentFactory).
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if (! in_array($user->role->key, $roles, true)) {
            abort(403, 'You do not have permission to access this section.');
        }

        return $next($request);
    }
}
