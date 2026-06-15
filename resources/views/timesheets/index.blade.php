@extends('layouts.app')

@php
use Illuminate\Support\Str;
@endphp

@section('title', 'Timesheets - Attendance')

@section('content')
  <div class="page-shell">
    <div class="attendance-page-actions">
      <a href="{{ route('dashboard') }}" class="btn btn-secondary page-back-btn">Back to Dashboard</a>
      {{-- <a href="#" id="logout" class="btn btn-secondary page-logout-btn">Logout</a> --}}
      {{-- <form action="{{ route('logout') }}" method="POST" class="btn btn-secondary page-logout-btn" >
    @csrf
    <button type="submit" class="btn ...">
        Logout
    </button>
</form> --}}
<a href="#" class="btn btn-secondary page-logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
    Logout
</a>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
</div>

    <header class="hero compact-hero">
      <div>
        <p class="eyebrow">Timesheets</p>
        <h1>Employee Timesheets</h1>
        <p class="hero-copy">Create and review timesheets based on your role.</p>
      </div>
      <div class="hero-actions">
        <a href="{{ route('attendance') }}" class="btn btn-primary">View Attendance</a>
      </div>
    </header>

    <section class="panel filter-panel">
      <div class="panel-header">
        <div>
          <h2>Create Timesheet</h2>
          <p class="panel-copy">Generate a unique report code and capture your work details.</p>
        </div>
      </div>

      <form method="POST" action="{{ route('timesheets.store') }}" class="filter-form">
        @csrf
        <label>
          <span>Report Code</span>
         {{-- <input type="text"
       name="report_code"
       value="{{ old('report_code', $existingCode ?? 'TS-' . strtoupper(Str::random(6))) }}"
       {{ $existingCode ? 'readonly' : '' }}
       required> --}}
       <input type="text"
       name="report_code"
       id="report_code"
       value="{{ old('report_code', $displayCode) }}"
       {{ $isExisting ? 'readonly' : '' }}
       required>

          {{-- <input type="text" name="report_code" value="{{ old('report_code', 'TS-' . strtoupper(Str::random(6))) }}" placeholder="Enter unique report code" required> --}}
          @error('report_code')<span class="error-message">{{ $message }}</span>@enderror
        </label>
        <label>
          <span>Date</span>
          <input type="date" name="report_date" value="{{ old('report_date', now()->toDateString()) }}" required>
          @error('report_date')<span class="error-message">{{ $message }}</span>@enderror
        </label>
        <label>
          <span>Project Name</span>
          <textarea name="project_name" rows="2" required>{{ old('project_name') }}</textarea>
          @error('project_name')<span class="error-message">{{ $message }}</span>@enderror
        </label>
        <label>
          <span>Functions</span>
          <textarea name="functions" rows="2" required>{{ old('functions') }}</textarea>
          @error('functions')<span class="error-message">{{ $message }}</span>@enderror
        </label>
        <label>
          <span>Status</span>
          <select name="status" required>
            <option value="Pending"{{ old('status') === 'Pending' ? ' selected' : '' }}>Pending</option>
            <option value="Done"{{ old('status') === 'Done' ? ' selected' : '' }}>Done</option>
          </select>
          @error('status')<span class="error-message">{{ $message }}</span>@enderror
        </label>
        <label>
          <span>Remark</span>
          <textarea name="remark" rows="2" >{{ old('remark') }}</textarea>
          @error('remark')<span class="error-message">{{ $message }}</span>@enderror
        </label>
        <button type="submit" class="btn btn-primary">Create Timesheet</button>
      </form>
    </section>

    <section class="panel table-panel">
      <div class="panel-header">
        <h2>Timesheet Records</h2>
      </div>
      <div class="table-wrap">
        <table class="attendance-table">
          <thead>
            <tr>
              <th>Report Code</th>
              <th>Date</th>
              <th>Status</th>
              <th>Project</th>
              <th>Employee</th>
              @if($isHr)
                <th>Department</th>
              @endif
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($reports as $report)

        {{-- @foreach($report->details as $detail) --}}
            <tr>
                <td><a href="{{ route('timesheets.show', $report->report_code) }}">{{ $report->report_code }}</a></td>
                <td>{{ $report->report_date->format('Y-m-d') }}</td>
                <td>{{ $detail->status ?? 'Pending' }}</td>
                <td>{{ $report->details->count() }}</td>
                {{-- {{ Str::limit($detail->project_name ?? '-', 40) }} --}}
                <td>{{ $report->name }}</td>
                @if($isHr)
                    <td>{{ $report->department_name }}</td>
                @endif
                <td><a href="{{ route('timesheets.show', $report->report_code) }}" class="btn btn-secondary">View</a></td>
            </tr>
        {{-- @endforeach --}}
    @empty
              <tr>
                <td colspan="{{ $isHr ? 7 : 6 }}" class="empty-state">No timesheets available.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>
@endsection
