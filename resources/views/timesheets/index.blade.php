@extends('layouts.app')

@section('title', 'Timesheets - Attendance')

@section('content')
  <div class="page-shell">
    <header class="hero compact-hero">
      <div>
        <p class="eyebrow">Timesheets</p>
        <h1>Employee Timesheets</h1>
        <p class="hero-copy">Create and review timesheets based on your role.</p>
      </div>
      <div class="hero-actions">
        <a href="{{ route('attendance') }}" class="btn btn-primary">Add Attendance</a>
      </div>
    </header>

    @if(session('success'))
      <div class="status-message">{{ session('success') }}</div>
    @endif

    <section class="panel filter-panel">
      <div class="panel-header">
        <div>
          <h2>Create Timesheet</h2>
          <p class="panel-copy">Add one or more work rows before saving.</p>
        </div>
      </div>

      @include('timesheets._form', [
        'timesheetId' => null,
        'displayCode' => $displayCode,
      ])
    </section>

    <section class="panel table-panel">
      <div class="panel-header">
        <div>
          <h2>Timesheet Records</h2>
          <p class="panel-copy">Detailed reports are available from each record.</p>
        </div>
      </div>

      @if($isHr)
        <form method="GET" action="{{ route('timesheets.index') }}" class="filter-form stacked-filter">
          <label>
            <span>Department</span>
            <select name="department_id">
              <option value="">All departments</option>
              @foreach($departments as $department)
                <option value="{{ $department->id }}" @selected((string) $selectedDepartmentId === (string) $department->id)>
                  {{ $department->department_name }}
                </option>
              @endforeach
            </select>
          </label>
          <button type="submit" class="btn btn-primary">Apply Filter</button>
          @if($selectedDepartmentId)
            <a href="{{ route('timesheets.index') }}" class="btn btn-secondary">Clear</a>
          @endif
        </form>
      @endif

      <div class="table-wrap">
        <table class="attendance-table">
          <thead>
            <tr>
              <th>Report Code</th>
              <th>Date</th>
              <th>Status</th>
              <th>Rows</th>
              <th>Employee</th>
              @if($isHr)
                <th>Department</th>
              @endif
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($reports as $report)
              @php
                $latestDetail = $report->details->last();
              @endphp
              <tr>
                <td>{{ $report->report_code }}</td>
                <td>{{ $report->report_date->format('Y-m-d') }}</td>
                <td>{{ $latestDetail?->status ?? 'Pending' }}</td>
                <td>{{ $report->details->count() }}</td>
                <td>{{ $report->name }}</td>
                @if($isHr)
                  <td>{{ $report->department_name ?? '-' }}</td>
                @endif
                <td><a href="{{ route('timesheets.show', $report->report_code) }}" class="btn btn-secondary">View</a></td>
              </tr>
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
