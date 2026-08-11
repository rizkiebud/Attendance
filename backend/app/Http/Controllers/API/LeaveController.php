<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $employee = auth()->user()->employee;

        $leaves = LeaveRequest::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $leaves,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis' => 'required|in:izin,sakit,cuti',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:500',
            'dokumen' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $employee = auth()->user()->employee;
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan',
            ], 404);
        }

        $dokumenPath = null;
        if ($request->hasFile('dokumen')) {
            $dokumenPath = $request->file('dokumen')->store('izin', 'public');
        }

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'jenis' => $request->jenis,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan,
            'dokumen' => $dokumenPath,
            'status' => 'menunggu',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permohonan berhasil diajukan',
            'data' => $leave,
        ], 201);
    }

    public function show($id)
    {
        $employee = auth()->user()->employee;

        $leave = LeaveRequest::where('employee_id', $employee->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $leave,
        ]);
    }

    public function pending()
    {
        if (!auth()->user()->isSupervisor()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya supervisor yang dapat menyetujui izin.',
            ], 403);
        }

        $leaves = LeaveRequest::with('employee.user')
            ->where('status', 'menunggu')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $leaves,
        ]);
    }

    public function approve(Request $request, $id)
    {
        if (!auth()->user()->isSupervisor()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya supervisor yang dapat menyetujui izin.',
            ], 403);
        }

        $leave = LeaveRequest::findOrFail($id);

        if ($leave->status !== 'menunggu') {
            return response()->json([
                'success' => false,
                'message' => 'Permohonan ini sudah diproses',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $leave->update([
            'status' => 'disetujui',
            'catatan_admin' => $request->catatan,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Update status absensi pada tanggal yang diajukan
        $tanggal = $leave->tanggal_mulai->copy();
        $defaultOfficeId = Office::where('aktif', true)->value('id') ?? 1;
        while ($tanggal->lte($leave->tanggal_selesai)) {
            Attendance::updateOrCreate(
                [
                    'employee_id' => $leave->employee_id,
                    'tanggal' => $tanggal->toDateString(),
                ],
                [
                    'office_id' => $defaultOfficeId,
                    'status' => ($leave->jenis === 'cuti') ? 'izin' : $leave->jenis,
                    'keterangan' => $leave->alasan,
                ]
            );
            $tanggal->addDay();
        }

        return response()->json([
            'success' => true,
            'message' => 'Permohonan berhasil disetujui',
            'data' => $leave->load('employee.user'),
        ]);
    }

    public function reject(Request $request, $id)
    {
        if (!auth()->user()->isSupervisor()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya supervisor yang dapat menyetujui izin.',
            ], 403);
        }

        $leave = LeaveRequest::findOrFail($id);

        if ($leave->status !== 'menunggu') {
            return response()->json([
                'success' => false,
                'message' => 'Permohonan ini sudah diproses',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'catatan' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Catatan wajib diisi saat menolak izin',
                'errors' => $validator->errors(),
            ], 422);
        }

        $leave->update([
            'status' => 'ditolak',
            'catatan_admin' => $request->catatan,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permohonan berhasil ditolak',
            'data' => $leave->load('employee.user'),
        ]);
    }
}
