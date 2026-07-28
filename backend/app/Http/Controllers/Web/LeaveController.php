<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::with('employee.user');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->jenis) {
            $query->where('jenis', $request->jenis);
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('web.leaves.index', compact('leaves'));
    }

    public function show(LeaveRequest $leave)
    {
        $leave->load(['employee.user', 'approvedBy']);
        return view('web.leaves.show', compact('leave'));
    }

    public function approve(Request $request, LeaveRequest $leave)
    {
        if ($leave->status !== 'menunggu') {
            return back()->with('error', 'Permohonan ini sudah diproses');
        }

        $leave->update([
            'status' => 'disetujui',
            'catatan_admin' => $request->catatan,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Update status absensi pada tanggal yang diajukan
        $tanggal = $leave->tanggal_mulai->copy();
        $defaultOfficeId = \App\Models\Office::where('aktif', true)->value('id') ?? 1;
        while ($tanggal->lte($leave->tanggal_selesai)) {
            Attendance::updateOrCreate(
                [
                    'employee_id' => $leave->employee_id,
                    'tanggal' => $tanggal->toDateString(),
                ],
                [
                    'office_id' => $defaultOfficeId,
                    'status' => $leave->jenis,
                    'keterangan' => $leave->alasan,
                ]
            );
            $tanggal->addDay();
        }

        return back()->with('success', 'Permohonan berhasil disetujui');
    }

    public function reject(Request $request, LeaveRequest $leave)
    {
        $request->validate(['catatan' => 'required|string']);

        if ($leave->status !== 'menunggu') {
            return back()->with('error', 'Permohonan ini sudah diproses');
        }

        $leave->update([
            'status' => 'ditolak',
            'catatan_admin' => $request->catatan,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Permohonan berhasil ditolak');
    }
}
