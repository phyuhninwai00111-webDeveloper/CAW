<nav class="app-navbar">
    <a href="{{ route('dashboard') }}" class="app-navbar-brand">Attendance System</a>

    <div class="app-navbar-links">
        <a href="{{ route('dashboard') }}" class="app-navbar-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
        <a href="{{ route('attendance') }}" class="app-navbar-link {{ request()->routeIs('attendance') ? 'is-active' : '' }}">Attendance</a>
        <a href="{{ route('timesheets.index') }}" class="app-navbar-link {{ request()->routeIs('timesheets.*') ? 'is-active' : '' }}">Timesheet</a>
        <form method="POST" action="{{ route('logout') }}" class="app-navbar-form">
            @csrf
            <button type="submit" class="app-navbar-link app-navbar-button">Logout</button>
        </form>
    </div>
</nav>
