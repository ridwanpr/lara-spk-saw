<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                abort(401, 'Unauthenticated.');
            }

            return redirect()->guest(route('login'));
        }

        // Expand any comma-separated or pipe-separated role arguments
        $parsedRoles = [];
        foreach ($roles as $role) {
            $split = preg_split('/[,|]/', $role, -1, PREG_SPLIT_NO_EMPTY);
            if ($split !== false) {
                $parsedRoles = array_merge($parsedRoles, $split);
            }
        }

        if (! empty($parsedRoles) && ! $user->hasRole($parsedRoles)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}
