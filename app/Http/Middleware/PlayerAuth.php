<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated using the 'player' guard
        if (!Auth::guard('player')->check()) {
            // Redirect to the login page if not authenticated
            return redirect()->route('player.login');
        }

        return $next($request);
    }
}
