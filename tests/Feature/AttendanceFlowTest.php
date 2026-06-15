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
}
