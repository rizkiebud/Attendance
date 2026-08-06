<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckJabatan
{
    /**
     * Handle request. Parameter: level minimal yang dibutuhkan.
     * Contoh: ->middleware('jabatan:manage') berarti minimal manage.
     */
    public function handle(Request $request, Closure $next, string $minLevel = 'view'): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('web.login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $level = $user->accessLevel();

        if (!$level) {
            return $this->denied($request);
        }

        $levelRank = ['view' => 0, 'manage' => 1, 'full' => 2];
        $needRank = $levelRank[$minLevel] ?? 0;

        if ($levelRank[$level] >= $needRank) {
            return $next($request);
        }

        return $this->denied($request);
    }

    protected function denied(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }
        return redirect()->route('web.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}