@extends('layouts.app')

@section('title', 'Register - Attendance')

@section('content')
    <div class="page-shell auth-shell">
        <section class="auth-card">
            <div class="auth-intro">
                <p class="eyebrow">Company Attendance</p>
                <h1>Create your account</h1>
                <p class="hero-copy">Set up your profile and join your department’s attendance system.</p>
            </div>

            @if ($errors->any())
                <div class="panel error-card" style="margin-bottom: 16px;">
                    <ul style="margin: 0; padding-left: 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="auth-form">
                @csrf{{-- cross site request forgery (token)--}}
                <label>
                    <span>Name</span>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Your full name" required autofocus>
                </label>

                <label>
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="name@company.com" required>
                </label>

                <label>
                    <span>Staff Code</span>
                    <input type="text" name="employee_code" value="{{ old('employee_code') }}" placeholder="Enter your code" required>
                </label>

                <label>
                    <span>Password</span>
                    <input type="password" name="password" placeholder="Create a password" required>
                </label>

                <label>
                    <span>Confirm Password</span>
                    <input type="password" name="password_confirmation" placeholder="Confirm your password" required>
                </label>

                <label>
                    <span>Department</span>
                    <select id="department_id" name="department_id">
                        <option value="">-- Select department --</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->department_name }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>Role</span>
                    <select id="role_id" name="role_id" required>
                        <option value="">-- Select role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                </label>

                <button type="submit" class="btn btn-primary">Register</button>
            </form>

            <p class="auth-footer">Already have an account? <a href="{{ route('login') }}">Login</a></p>
        </section>
    </div>
@endsection
