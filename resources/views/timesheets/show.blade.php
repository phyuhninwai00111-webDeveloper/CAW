@extends('layouts.app')

@section('title', 'Timesheet Details - Attendance')

@section('content')
  @php
    $selectedDetail = $canEdit && request()->boolean('edit')
        ? $report->details->firstWhere('id', (int) request('detail_id'))
        : null;
  @endphp

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

    @if($selectedDetail)
      <section class="panel filter-panel" id="edit-timesheet">
        <div class="panel-header">
          <div>
            <h2>Edit Timesheet</h2>
            <p class="panel-copy">Update existing rows or add more rows before saving.</p>
          </div>
        </div>

        @include('timesheets._form', [
          'timesheetId' => $report->report_code,
          'report' => $report,
          'selectedDetail' => $selectedDetail,
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

      <div class="table-wrap personal-timesheet-scroll">
        <table class="attendance-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Project Name</th>
              <th>Functions</th>
              <th>Status</th>
              <th>Remark</th>
              {{-- @if($canEdit) --}}
                @if(request()->query('mode') !== 'view')
                <th>Action</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @forelse($report->details as $detail)
              <tr>
                <td>{{ $report->report_date->format('Y-m-d') }}</td>
                <td class="text-wrap">{{ $detail->project_name }}</td>
                <td class="text-wrap">{{ $detail->functions }}</td>
                <td>{{ $detail->status }}</td>
                <td class="text-wrap">{{ $detail->remark ?? '-' }}</td>
                @if(request()->query('mode') !== 'view')
                  <td><a href="{{ route('timesheets.show',
                   ['report_code' => $report->report_code, 'edit' => 1, 'detail_id' => $detail->id]). '?mode=edit' }}
                   #edit-timesheet" class="btn btn-secondary btn-sm">Edit</a></td>
                @endif
              </tr>
            @empty
              <tr>

                <td colspan="{{ $canEdit ? 6 : 5 }}" class="empty-state">No detail rows have been added.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>
@endsection
