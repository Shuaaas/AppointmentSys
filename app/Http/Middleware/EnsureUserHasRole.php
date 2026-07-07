<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Usage in routes: ->middleware('role:admin')
     * or multiple allowed roles: ->middleware('role:hr,admin')
     *
     * Register the alias in bootstrap/app.php (Laravel 11/12):
     *   $middleware->alias(['role' => \App\Http\Middleware\EnsureUserHasRole::class]);
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (! $user->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'This account has been deactivated. Contact your Admin.']);
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403, 'You do not have access to this section.');
        }

        return $next($request);
    }
}