<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Pembatas akses berdasarkan role pengguna yang sedang login.
 * Contoh: ->middleware(EnsureRole::class . ':admin')  atau  ':admin,petugas'
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                abort(401, 'Sesi berakhir. Silakan masuk kembali.');
            }

            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses untuk tindakan ini.');
        }

        return $next($request);
    }
}
