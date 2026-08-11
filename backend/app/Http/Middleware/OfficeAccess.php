<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OfficeAccess
{
    /**
     * Akses Kantor & Lokasi: admin (full) atau role HRD.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('web.login');
        }

        if (auth()->user()->canManageOffices()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
        return redirect()->route('web.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}