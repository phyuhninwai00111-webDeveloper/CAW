<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    protected function createAuthenticatedUser(): User
    {
        $department = Department::create(['department_name' => 'Operations']);
        $this->seedRoles();

        return User::factory()->create([
            'department_id' => $department->id,
            'role_id' => 3,
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

    public function test_hr_dashboard_summary_shows_all_departments(): void
    {
        $this->seedRoles();
        $departmentA = Department::create(['department_name' => 'Engineering']);
        $departmentB = Department::create(['department_name' => 'Support']);

        $hr = User::factory()->create([
            'department_id' => $departmentA->id,
            'role_id' => 1,
            'employee_code' => 'EMP-HR',
        ]);
        $staffA = User::factory()->create([
            'department_id' => $departmentA->id,
            'role_id' => 3,
            'employee_code' => 'EMP-A',
        ]);
        $staffB = User::factory()->create([
            'department_id' => $departmentB->id,
            'role_id' => 3,
            'employee_code' => 'EMP-B',
        ]);

        Attendance::create([
            'employee_code' => $staffA->employee_code,
            'attendance_date' => now()->toDateString(),
            'check_in' => now()->setTime(8, 30),
        ]);
        Attendance::create([
            'employee_code' => $staffB->employee_code,
            'attendance_date' => now()->toDateString(),
            'check_in' => now()->setTime(9, 30),
        ]);

        $this->actingAs($hr);

        $response = $this->getJson('/dashboard/summary');

        $response->assertStatus(200);
        $response->assertJson([
            'role_id' => 1,
            'total' => 2,
            'late' => 1,
        ]);
    }

    public function test_hod_dashboard_summary_shows_department_only(): void
    {
        $this->seedRoles();
        $departmentA = Department::create(['department_name' => 'Engineering']);
        $departmentB = Department::create(['department_name' => 'Support']);

        $hod = User::factory()->create([
            'department_id' => $departmentA->id,
            'role_id' => 2,
            'employee_code' => 'EMP-HOD',
        ]);
        $staffA = User::factory()->create([
            'department_id' => $departmentA->id,
            'role_id' => 3,
            'employee_code' => 'EMP-A',
        ]);
        $staffB = User::factory()->create([
            'department_id' => $departmentB->id,
            'role_id' => 3,
            'employee_code' => 'EMP-B',
        ]);

        Attendance::create([
            'employee_code' => $staffA->employee_code,
            'attendance_date' => now()->toDateString(),
            'check_in' => now()->setTime(8, 30),
        ]);
        Attendance::create([
            'employee_code' => $staffB->employee_code,
            'attendance_date' => now()->toDateString(),
            'check_in' => now()->setTime(8, 45),
        ]);

        $this->actingAs($hod);

        $response = $this->getJson('/dashboard/summary');

        $response->assertStatus(200);
        $response->assertJson([
            'role_id' => 2,
            'total' => 1,
            'late' => 0,
        ]);
    }

    public function test_staff_dashboard_summary_shows_own_record_only(): void
    {
        $this->seedRoles();
        $departmentA = Department::create(['department_name' => 'Engineering']);
        $departmentB = Department::create(['department_name' => 'Support']);

        $staffA = User::factory()->create([
            'department_id' => $departmentA->id,
            'role_id' => 3,
            'employee_code' => 'EMP-A',
        ]);
        $staffB = User::factory()->create([
            'department_id' => $departmentB->id,
            'role_id' => 3,
            'employee_code' => 'EMP-B',
        ]);

        Attendance::create([
            'employee_code' => $staffA->employee_code,
            'attendance_date' => now()->toDateString(),
            'check_in' => now()->setTime(8, 30),
        ]);
        Attendance::create([
            'employee_code' => $staffB->employee_code,
            'attendance_date' => now()->toDateString(),
            'check_in' => now()->setTime(9, 30),
        ]);

        $this->actingAs($staffA);

        $response = $this->getJson('/dashboard/summary');

        $response->assertStatus(200);
        $response->assertJson([
            'role_id' => 3,
            'total' => 1,
            'late' => 0,
        ]);
    }
}
