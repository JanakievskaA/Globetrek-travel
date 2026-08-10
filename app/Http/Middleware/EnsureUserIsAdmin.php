<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the admin-only corners of the panel — staff accounts, homepage
 * content and every delete. Managers reach the panel but not these.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Only an administrator can do that.');
        }

        return $next($request);
    }
}
