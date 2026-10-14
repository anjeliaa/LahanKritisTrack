<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Contoh pemakaian: ->middleware(EnsureRole::class . ':admin') */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if ($request->session()->get('role') !== $role) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        return $next($request);
    }
}
