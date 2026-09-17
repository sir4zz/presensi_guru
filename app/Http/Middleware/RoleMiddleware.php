<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('guru.login');
        }

        if (!in_array(auth()->user()->role, $roles)) {
            abort(403, 'Tidak memiliki akses.');
        }

        return $next($request);
    }
}
