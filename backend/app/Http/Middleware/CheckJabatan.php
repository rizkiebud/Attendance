<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckJabatan
{
    /**
     * Level akses berdasarkan jabatan & role.
     *
     * - admin -> full
     * - Kepala Seksi -> manage
     * - Staf -> view-only
     */
    const LEVELS = [
        'admin'         => ['full', 'manage', 'view'],
        'Kepala Seksi'  => ['manage', 'view'],
        'Staf'          => ['view'],
    ];

    /**
     * Handle request. Parameter: level minimal yang dibutuhkan.
     * Contoh: ->middleware('jabatan:manage') berarti minimal manage.
     */
    public function handle(Request $request, Closure $next, string $minLevel = 'view'): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('web.login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userLevels = self::LEVELS['admin']; // default

        if ($user->isAdmin()) {
            $userLevels = self::LEVELS['admin'];
        } elseif ($user->employee && $user->employee->jabatan) {
            $userLevels = self::LEVELS[$user->employee->jabatan] ?? ['view'];
        } else {
            return $this->denied($request);
        }

        $levelRank = ['view' => 0, 'manage' => 1, 'full' => 2];
        $userRank = max(array_map(fn($l) => $levelRank[$l] ?? -1, $userLevels));
        $needRank = $levelRank[$minLevel] ?? 0;

        if ($userRank >= $needRank) {
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