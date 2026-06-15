<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\DailyReport;
use App\Models\DailyReportDetail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimesheetFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function createDepartment(): Department
    {
        return Department::create(['department_name' => 'Engineering']);
    }

    protected function createRole(string $name): Role
    {
        return Role::create(['role_name' => $name]);
    }

    public function test_employee_can_create_timesheet_and_attendance_links_to_report()
    {
        $department = $this->createDepartment();
        $role = $this->createRole('Staff');

        $user = User::factory()->create([
            'department_id' => $department->id,
            'role_id' => $role->id,
            'employee_code' => 'EMP-T001',
        ]);

        $this->actingAs($user);

        Attendance::create([
            'employee_code' => $user->employee_code,
            'attendance_date' => now()->toDateString(),
            'check_in' => now()->subHours(8),
            'created_at' => now(),
        ]);

        $response = $this->post('/timesheets', [
            'report_code' => 'TS-TEST-01',
            'report_date' => now()->toDateString(),
            'project_name' => 'Project A',
            'functions' => 'Development work',
            'status' => 'Done',
            'remark' => 'Completed tasks for the day.',
        ]);

        $response->assertRedirect(route('timesheets.show', 'TS-TEST-01'));

        $report = DailyReport::where('report_code', 'TS-TEST-01')->first();
        $this->assertNotNull($report);
        $this->assertEquals($user->employee_code, $report->employee_code);

        $this->assertDatabaseHas('attendance', [
            'employee_code' => $user->employee_code,
            'attendance_date' => now()->toDateString(),
            'report_code' => 'TS-TEST-01',
        ]);

        $this->assertDatabaseHas('daily_report_details', [
            'report_code' => 'TS-TEST-01',
            'project_name' => 'Project A',
        ]);
    }

    public function test_hr_can_view_all_timesheets_and_hod_sees_department_only()
    {
        $departmentA = $this->createDepartment();
        $departmentB = Department::create(['department_name' => 'Support']);
        $roleHr = $this->createRole('HR');
        $roleHod = $this->createRole('HOD');
        $roleStaff = $this->createRole('Staff');

        $hr = User::factory()->create(['department_id' => $departmentA->id, 'role_id' => $roleHr->id, 'employee_code' => 'EMP-HR']);
        $hod = User::factory()->create(['department_id' => $departmentA->id, 'role_id' => $roleHod->id, 'employee_code' => 'EMP-HOD']);
        $staff = User::factory()->create(['department_id' => $departmentB->id, 'role_id' => $roleStaff->id, 'employee_code' => 'EMP-STAFF']);

        DailyReport::create(['employee_code' => $hr->employee_code, 'report_code' => 'TS-HR', 'report_date' => now()->toDateString()]);
        DailyReportDetail::create(['report_code' => 'TS-HR', 'project_name' => 'HR Work', 'functions' => 'Review', 'status' => 'Done', 'remark' => 'HR tasks']);

        DailyReport::create(['employee_code' => $staff->employee_code, 'report_code' => 'TS-STAFF', 'report_date' => now()->toDateString()]);
        DailyReportDetail::create(['report_code' => 'TS-STAFF', 'project_name' => 'Support Work', 'functions' => 'Assist', 'status' => 'Pending', 'remark' => 'Pending review']);

        $this->actingAs($hr);
        $responseHr = $this->get('/timesheets');
        $responseHr->assertStatus(200);
        $responseHr->assertSee('TS-HR');
        $responseHr->assertSee('TS-STAFF');

        $this->actingAs($hod);
        $responseHod = $this->get('/timesheets');
        $responseHod->assertStatus(200);
        $responseHod->assertSee('TS-HR');
        $responseHod->assertDontSee('TS-STAFF');
    }
}
