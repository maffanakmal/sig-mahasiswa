<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isGuest = !session()->has('loggedInUser');
        $isLoginPage = $request->is('/');

        if ($isGuest && !$isLoginPage) {
            // Belum login dan mencoba akses halaman lain
            return redirect('/');
        }

        if (!$isGuest && $isLoginPage) {
            // Sudah login, tapi mengakses halaman login
            return redirect('/dashboard'); // atau halaman lain setelah login
        }
        
        return $next($request);
    }
}
