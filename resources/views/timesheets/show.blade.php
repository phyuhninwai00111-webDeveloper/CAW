@extends('layouts.app')

@section('title', 'Timesheet Details - Attendance')

@section('content')
  <div class="page-shell">
    <header class="hero compact-hero">
      <div>
        <p class="eyebrow">Timesheet Detail</p>
        <h1>{{ $report->report_code }}</h1>
        <p class="hero-copy">{{ $report->employee->name }} - {{ $report->report_date->format('Y-m-d') }}</p>
      </div>
      <div class="hero-actions">
        <a href="{{ route('timesheets.index') }}" class="btn btn-secondary">Back to Timesheets</a>
        <a href="{{ route('attendance') }}" class="btn btn-primary">View Attendance</a>
      </div>
    </header>

    @if(session('success'))
      <div class="status-message">{{ session('success') }}</div>
    @endif

    @if($canEdit)
      <section class="panel filter-panel">
        <div class="panel-header">
          <div>
            <h2>Edit Timesheet</h2>
            <p class="panel-copy">Update existing rows or add more rows before saving.</p>
          </div>
        </div>

        @include('timesheets._form', [
          'timesheetId' => $report->report_code,
          'report' => $report,
        ])
      </section>
    @endif

    <section class="panel table-panel">
      <div class="panel-header">
        <div>
          <h2>Detailed Report</h2>
          <p class="panel-copy">{{ $report->employee->department->department_name ?? 'No department' }}</p>
        </div>
        <span class="badge">{{ $report->details->count() }} {{ Str::plural('row', $report->details->count()) }}</span>
      </div>

      <div class="table-wrap">
        <table class="attendance-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Project Name</th>
              <th>Functions</th>
              <th>Status</th>
              <th>Remark</th>
            </tr>
          </thead>
          <tbody>
            @forelse($report->details as $detail)
              <tr>
                <td>{{ $report->report_date->format('Y-m-d') }}</td>
                <td>{{ $detail->project_name }}</td>
                <td>{{ $detail->functions }}</td>
                <td>{{ $detail->status }}</td>
                <td>{{ $detail->remark ?? '-' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="empty-state">No detail rows have been added.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>
@endsection
