<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya admin yang dapat mengakses.',
                ], 403);
            }
            return redirect()->route('web.login')->with('error', 'Anda tidak memiliki akses admin.');
        }

        return $next($request);
    }
}
