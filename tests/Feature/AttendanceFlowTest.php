<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function createAuthenticatedUser(): User
    {
        $department = Department::create(['department_name' => 'Operations']);
        $role = Role::create(['role_name' => 'Employee']);

        return User::factory()->create([
            'department_id' => $department->id,
            'role_id' => $role->id,
            'employee_code' => 'EMP-3003',
        ]);
    }

    public function test_attendance_page_requires_authentication(): void
    {
        $response = $this->get('/attendance');

        $response->assertRedirect('/login');
    }

    public function test_user_can_check_in_and_check_out(): void
    {
        $user = $this->createAuthenticatedUser();

        $this->actingAs($user);

        $checkInResponse = $this->postJson('/attendance/check-in');
        $checkInResponse->assertStatus(200);
        $checkInResponse->assertJson(['success' => true]);

        $attendance = Attendance::where('employee_code', $user->employee_code)->first();
        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->check_in);
        $this->assertNull($attendance->check_out);

        $checkOutResponse = $this->postJson('/attendance/check-out');
        $checkOutResponse->assertStatus(200);
        $checkOutResponse->assertJson(['success' => true]);

        $attendance->refresh();
        $this->assertNotNull($attendance->check_out);
    }

    public function test_dashboard_summary_returns_attendance_counts(): void
    {
        $user = $this->createAuthenticatedUser();

        Attendance::create([
            'employee_code' => $user->employee_code,
            'attendance_date' => now()->subDay()->toDateString(),
            'check_in' => now()->subDay(),
            'check_out' => now()->subDay()->addHours(8),
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/dashboard/summary');

        $response->assertStatus(200);
        $response->assertJson(['total' => 1]);
    }

    public function test_hr_can_switch_between_my_history_and_all_records(): void
    {
        $departmentA = Department::create(['department_name' => 'Operations']);
        $departmentB = Department::create(['department_name' => 'Support']);
        $roleHr = Role::create(['role_name' => 'HR']);
        Role::create(['role_name' => 'HOD']);
        $roleStaff = Role::create(['role_name' => 'Staff']);

        $hr = User::factory()->create(['department_id' => $departmentA->id, 'role_id' => $roleHr->id, 'employee_code' => 'EMP-HR']);
        $staff = User::factory()->create(['department_id' => $departmentB->id, 'role_id' => $roleStaff->id, 'employee_code' => 'EMP-STAFF']);

        Attendance::create(['employee_code' => $hr->employee_code, 'attendance_date' => now()->toDateString(), 'check_in' => now()]);
        Attendance::create(['employee_code' => $staff->employee_code, 'attendance_date' => now()->toDateString(), 'check_in' => now()]);

        $this->actingAs($hr);

        $myHistory = $this->getJson('/attendance/data?scope=my');
        $myHistory->assertStatus(200);
        $this->assertCount(1, $myHistory->json('data'));
        $this->assertSame($hr->name, $myHistory->json('data.0.name'));

        $allRecords = $this->getJson('/attendance/data?scope=all');
        $allRecords->assertStatus(200);
        $this->assertCount(2, $allRecords->json('data'));
    }

    public function test_hod_can_switch_between_my_history_and_department_records(): void
    {
        $departmentA = Department::create(['department_name' => 'Operations']);
        $departmentB = Department::create(['department_name' => 'Support']);
        Role::create(['role_name' => 'HR']);
        $roleHod = Role::create(['role_name' => 'HOD']);
        $roleStaff = Role::create(['role_name' => 'Staff']);

        $hod = User::factory()->create(['department_id' => $departmentA->id, 'role_id' => $roleHod->id, 'employee_code' => 'EMP-HOD']);
        $departmentStaff = User::factory()->create(['department_id' => $departmentA->id, 'role_id' => $roleStaff->id, 'employee_code' => 'EMP-DEPT']);
        $otherStaff = User::factory()->create(['department_id' => $departmentB->id, 'role_id' => $roleStaff->id, 'employee_code' => 'EMP-OTHER']);

        Attendance::create(['employee_code' => $hod->employee_code, 'attendance_date' => now()->toDateString(), 'check_in' => now()]);
        Attendance::create(['employee_code' => $departmentStaff->employee_code, 'attendance_date' => now()->toDateString(), 'check_in' => now()]);
        Attendance::create(['employee_code' => $otherStaff->employee_code, 'attendance_date' => now()->toDateString(), 'check_in' => now()]);

        $this->actingAs($hod);

        $myHistory = $this->getJson('/attendance/data?scope=my');
        $myHistory->assertStatus(200);
        $this->assertCount(1, $myHistory->json('data'));
        $this->assertSame($hod->name, $myHistory->json('data.0.name'));

        $allRecords = $this->getJson('/attendance/data?scope=all');
        $allRecords->assertStatus(200);
        $this->assertCount(2, $allRecords->json('data'));
        $names = collect($allRecords->json('data'))->pluck('name');
        $this->assertTrue($names->contains($hod->name));
        $this->assertTrue($names->contains($departmentStaff->name));
        $this->assertFalse($names->contains($otherStaff->name));
    }
}
