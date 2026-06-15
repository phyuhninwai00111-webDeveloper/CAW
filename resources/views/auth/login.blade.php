@extends('layouts.app')

@section('title', 'Login - Attendance')

@section('content')
    <div class="page-shell auth-shell">
        <section class="auth-card">
            <div class="auth-intro">
                <p class="eyebrow">Company Attendance</p>
                <h1>Welcome</h1>
                <p class="hero-copy">Sign in to review team attendance and monitor late entries.</p>
            </div>

            @if (session('status'))
                <div class="panel" style="margin-bottom: 16px;">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="panel error-card" style="margin-bottom: 16px;">
                    <ul style="margin: 0; padding-left: 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

                <label>
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="name@company.com" required autofocus>
                </label>

                <label>
                    <span>Password</span>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </label>

                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <p class="auth-footer">Don't have an account? <a href="{{ route('register') }}">Register</a></p>
        </section>
    </div>
@endsection
