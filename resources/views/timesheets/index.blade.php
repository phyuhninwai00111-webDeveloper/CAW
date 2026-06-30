@extends('layouts.app')

@push('head')
  @include('components.datatables')
@endpush

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
        <a href="{{ route('attendance') }}" class="btn btn-primary btn-sm">Add Attendance</a>
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

    <section class="panel table-panel" id="filter-section">
      <div class="panel-header">
        <div>
          <h2>Timesheet Records</h2>
          <p class="panel-copy">Detailed reports are available from each record.</p>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" data-export-table="#timesheet-record">Export Excel</button>
      </div>

      <form method="GET" action="{{ route('timesheets.index') }}#filter-section" class="filter-form stacked-filter">
        @if($isHr)
          <div class="filter-field">
            <label>
              <span>Department</span>
              <select name="department_id" class="form-select d-inline-block form-select-sm" >
                <option value="">All departments</option>
                @foreach($departments as $department)
                  <option value="{{ $department->id }}" @selected((string) $selectedDepartmentId === (string) $department->id)>
                    {{ $department->department_name }}
                  </option>
                @endforeach
              </select>
            </label>
          </div>
        @endif
        <label>
          <span>Start Date</span>
          <input type="date" name="start_date" value="{{ $selectedStartDate }}">
        </label>
        <label>
          <span>End Date</span>
          <input type="date" name="end_date" value="{{ $selectedEndDate }}">
        </label>
        <button type="submit" class="btn btn-primary">Apply Filter</button>
        @if($selectedDepartmentId || $selectedStartDate || $selectedEndDate)
          <a href="{{ route('timesheets.index') }}#filter-section" class="btn btn-secondary btn-sm" style="padding: 6px 19px !important;">Clear</a>
        @endif
      </form>
      <div class="table-wrap">
        <table class="attendance-table" id="timesheet-record">
          <thead>
            <tr>
              {{-- <th>Report Code</th> --}}
              <th>Date</th>
              <th>Status</th>
              <th>Records</th>
              <th>Employee</th>
              @if($isHr)
                <th>Department</th>
              @endif
              @if(request()->query('mode') !== 'view')
              <th data-export-ignore>Action</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @forelse($reports as $report)
              @php
                $latestDetail = $report->details->last();
              @endphp
              <tr>
                {{-- <td>{{ $report->report_code }}</td> --}}
                <td>{{ $report->report_date->format('Y-m-d') }}</td>
                <td>{{ $latestDetail?->status ?? 'Pending' }}</td>
                <td>{{ $report->details->count() }}</td>
                <td>{{ $report->name }}</td>
                @if($isHr)
                  <td>{{ $report->department_name ?? '-' }}</td>
                @endif
                @if(request()->query('mode') !== 'view')
                  <td data-export-ignore><a href="{{ route('timesheets.show', [$report->report_code, 'mode' => 'view']) }}#edit-timesheet" class="btn btn-secondary btn-sm" style="padding: 6px 12px;">View</a></td>
                @endif
              </tr>
            @empty
              @php
                $emptyColumnCount = 4 + ($isHr ? 1 : 0) + (request()->query('mode') !== 'view' ? 1 : 0);
              @endphp
              <tr>
                <td class="empty-state">No timesheets available.</td>
                @for ($i = 1; $i < $emptyColumnCount; $i++)
                  <td></td>
                @endfor
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>
@endsection

@push('scripts')
  @include('timesheets._export_script')
  @include('components.datatables-init', ['selector' => '#timesheet-record'])
@endpush
