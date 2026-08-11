<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveApprovalTest extends TestCase
{
    use RefreshDatabase;

    private Role $supervisorRole;
    private User $supervisor;
    private User $karyawan;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supervisorRole = Role::firstOrCreate(['name' => 'supervisor'], [
            'label' => 'Supervisor',
            'level' => 'manage',
        ]);
        Role::firstOrCreate(['name' => 'karyawan'], ['label' => 'Karyawan', 'level' => 'view']);
        Role::firstOrCreate(['name' => 'admin'], ['label' => 'Admin', 'level' => 'full']);

        $this->supervisor = User::create([
            'name' => 'Atasan',
            'email' => 'supervisor@test.com',
            'username' => 'supervisor',
            'password' => 'password',
            'role' => 'karyawan',
            'role_id' => $this->supervisorRole->id,
        ]);

        $this->karyawan = User::create([
            'name' => 'Bawahan',
            'email' => 'karyawan@test.com',
            'username' => 'karyawan',
            'password' => 'password',
            'role' => 'karyawan',
            'role_id' => Role::where('name', 'karyawan')->first()->id,
        ]);

        $this->employee = Employee::create([
            'user_id' => $this->karyawan->id,
            'nama' => 'Bawahan',
            'aktif' => true,
        ]);

        Office::create([
            'nama' => 'Kantor',
            'latitude' => '-6.200000',
            'longitude' => '106.816666',
            'aktif' => true,
        ]);
    }

    private function authAs(User $user): string
    {
        return auth()->guard('api')->login($user);
    }

    private function createLeave(string $status = 'menunggu'): LeaveRequest
    {
        return LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'jenis' => 'izin',
            'tanggal_mulai' => now()->addDay()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'alasan' => 'Keperluan keluarga',
            'status' => $status,
        ]);
    }

    public function test_supervisor_can_approve_leave_and_create_attendance(): void
    {
        $leave = $this->createLeave();
        $token = $this->authAs($this->supervisor);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson("/api/leave/{$leave->id}/approve", ['catatan' => 'OK']);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'disetujui');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'disetujui',
            'approved_by' => $this->supervisor->id,
        ]);

        // Attendance dibuat untuk tanggal izin
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $this->employee->id,
            'status' => 'izin',
        ]);
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $this->employee->id,
            'tanggal' => $leave->tanggal_mulai->toDateString() . ' 00:00:00',
        ]);
    }

    public function test_supervisor_can_reject_leave(): void
    {
        $leave = $this->createLeave();
        $token = $this->authAs($this->supervisor);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson("/api/leave/{$leave->id}/reject", ['catatan' => 'Dokumen kurang']);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'ditolak');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'ditolak',
            'catatan_admin' => 'Dokumen kurang',
        ]);
    }

    public function test_reject_requires_catatan(): void
    {
        $leave = $this->createLeave();
        $token = $this->authAs($this->supervisor);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson("/api/leave/{$leave->id}/reject", []);

        $response->assertStatus(422);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'menunggu',
        ]);
    }

    public function test_non_supervisor_cannot_approve(): void
    {
        $leave = $this->createLeave();
        $token = $this->authAs($this->karyawan);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson("/api/leave/{$leave->id}/approve", ['catatan' => 'OK']);

        $response->assertStatus(403);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'menunggu',
        ]);
    }

    public function test_already_processed_leave_cannot_be_approved_again(): void
    {
        $leave = $this->createLeave('disetujui');
        $token = $this->authAs($this->supervisor);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson("/api/leave/{$leave->id}/approve", ['catatan' => 'OK']);

        $response->assertStatus(422);
    }

    public function test_pending_returns_only_menunggu_leaves(): void
    {
        $menunggu = $this->createLeave('menunggu');
        $approved = $this->createLeave('disetujui');
        $token = $this->authAs($this->supervisor);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/leave/pending/list');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $ids = collect($response->json('data.data'))->pluck('id')->all();
        $this->assertContains($menunggu->id, $ids);
        $this->assertNotContains($approved->id, $ids);
    }
}