<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('loggedInUser')) {
            $user = User::where('user_uuid', session('loggedInUser.user_uuid'))->first();

            if ($user && $user->is_active) {
                $timeoutMinutes = 30;
                if ($user->last_active && now()->diffInMinutes($user->last_active) >= $timeoutMinutes) {
                    $user->is_active = 0;
                    $user->timestamps = false;
                    $user->save();
                    session()->forget('loggedInUser');

                    if ($request->expectsJson()) {
                        return response()->json([
                            "status" => 440,
                            "title" => "Session Expired",
                            "message" => "Sesi anda telah habis. Silakan login kembali.",
                            "icon" => "info"
                        ], 440);
                    } else {
                        return redirect()->route('auth.login')->with('session_expired', 'Sesi anda telah habis. Silakan login kembali.');
                    }
                } else {
                    $user->last_active = now();
                    $user->timestamps = false;
                    $user->save();
                }
            }
        }

        return $next($request);
    }
}
