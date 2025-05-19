<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = session('loggedInUser');

        if (!$user) {
            // Belum login, bisa redirect ke login atau abort
            return redirect('/');
        }

        // Cek apakah role user ada di dalam array role yang diizinkan
        if (!in_array($user['role'], $roles)) {
            abort(403, 'Akses ditolak: Anda tidak punya izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
