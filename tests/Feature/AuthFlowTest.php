<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_is_visible(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Register');
        $response->assertSee('Employee Code');
    }

    public function test_user_can_register(): void
    {
        $department = Department::create(['department_name' => 'Engineering']);
        $role = Role::create(['role_name' => 'Employee']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'employee_code' => 'EMP-1001',
            'department_id' => $department->id,
            'role_id' => $role->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'employee_code' => 'EMP-1001',
            'department_id' => $department->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_user_can_login(): void
    {
        $department = Department::create(['department_name' => 'Sales']);
        $role = Role::create(['role_name' => 'Employee']);

        $user = User::factory()->create([
            'email' => 'login@example.com',
            'employee_code' => 'EMP-2002',
            'department_id' => $department->id,
            'role_id' => $role->id,
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }
}
