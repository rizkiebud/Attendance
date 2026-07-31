<?php

namespace App\Http\Controllers\Web;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

/**
 * Filter query scope by departemen for non-admin users.
 */
trait FilterByDepartemen
{
    protected function departemenFilter(): ?string
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user && $user->isAdmin()) return null;
        return $user->employee?->departemen;
    }

    protected function filterEmployeeQuery($query)
    {
        $dep = $this->departemenFilter();
        if ($dep) $query->where('departemen', $dep);
        return $query;
    }

    protected function filterAttendanceQuery($query)
    {
        $dep = $this->departemenFilter();
        if ($dep) $query->whereHas('employee', fn($q) => $q->where('departemen', $dep));
        return $query;
    }

    protected function filterLeaveQuery($query)
    {
        $dep = $this->departemenFilter();
        if ($dep) $query->whereHas('employee', fn($q) => $q->where('departemen', $dep));
        return $query;
    }
}