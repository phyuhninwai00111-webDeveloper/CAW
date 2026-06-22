@extends('layouts.app')

@section('title', 'Personal Timesheet - Attendance')

@section('content')
  <div class="page-shell">
    <header class="hero compact-hero">
      <div>
        <p class="eyebrow">Personal Timesheet</p>
        <h1>My Timesheet Records</h1>
        <p class="hero-copy">
          @if($selectedStartDate || $selectedEndDate)
            Showing records
            @if($selectedStartDate)
              from {{ \Carbon\Carbon::parse($selectedStartDate)->format('Y-m-d') }}
            @endif
            @if($selectedEndDate)
              to {{ \Carbon\Carbon::parse($selectedEndDate)->format('Y-m-d') }}
            @endif
            .
          @else
            Showing {{ $defaultStartDate->format('Y-m-d') }} to {{ $defaultEndDate->format('Y-m-d') }}.
          @endif
        </p>
      </div>
      <div class="hero-actions">
        <a href="{{ route('timesheets.index') }}" class="btn btn-secondary">All Timesheets</a>
      </div>
    </header>

    @if(session('success'))
      <div class="status-message">{{ session('success') }}</div>
    @endif

    <section class="panel table-panel" id="personal-filter">
      <div class="panel-header">
        <div>
          <h2>Filter Records</h2>
          <p class="panel-copy">Choose a date to find your timesheet for that day.</p>
        </div>
      </div>

      <form method="GET" action="{{ route('timesheets.personal') }}#personal-filter" class="filter-form stacked-filter">
        <label>
          <span>Start Date</span>
          <input type="date" name="start_date" value="{{ $selectedStartDate ?? $defaultStartDate->format('Y-m-d') }}">
        </label>
        <label>
          <span>End Date</span>
          <input type="date" name="end_date" value="{{ $selectedEndDate ?? $defaultEndDate->format('Y-m-d') }}">
        </label>
        <button type="submit" class="btn btn-primary">Apply Filter</button>
        @if($selectedStartDate || $selectedEndDate)
          <a href="{{ route('timesheets.personal') }}#personal-filter" class="btn btn-secondary btn-sm">Clear</a>
        @endif
      </form>
    </section>

    <section class="panel table-panel">
      <div class="panel-header">
        <div>
          <h2>Timesheet Records</h2>
          <p class="panel-copy">Your saved report rows are listed below.</p>
        </div>
        <div class="hero-actions">
          {{-- <span class="badge">{{ $reports->count() }} {{ Str::plural('record', $reports->count()) }}</span> --}}
          <button type="button" class="btn btn-secondary btn-sm" data-export-table="#timesheet-record">Export Excel</button>
        </div>
      </div>

      <div class="table-wrap personal-timesheet-scroll">
        <table class="attendance-table" id="timesheet-record">
          <thead>
            <tr>
              <th>Date</th>
              <th>Project Name</th>
              <th>Functions</th>
              <th>Status</th>
              <th>Remark</th>
              <th data-export-ignore>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($reports as $report)
              @forelse($report->details as $detail)
                <tr>
                  <td>{{ $report->report_date->format('Y-m-d') }}</td>
                  <td class="text-wrap">{{ $detail->project_name }}</td>
                  <td class="text-wrap">{{ $detail->functions }}</td>
                  <td>{{ $detail->status }}</td>
                  <td class="text-wrap">{{ $detail->remark ?? '-' }}</td>
                  <td data-export-ignore>
                    <a href="{{ route('timesheets.show', ['report_code' => $report->report_code, 'edit' => 1, 'detail_id' => $detail->id, 'mode' => 'edit']) }}#edit-timesheet" class="btn btn-secondary btn-sm">Edit</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td>{{ $report->report_date->format('Y-m-d') }}</td>
                  <td colspan="4" class="empty-state">No detail rows have been added.</td>
                  <td data-export-ignore>
                    <a href="{{ route('timesheets.show', $report->report_code) }}" class="btn btn-secondary btn-sm">View</a>
                  </td>
                </tr>
              @endforelse
            @empty
              <tr>
                <td colspan="6" class="empty-state">No timesheets available.</td>
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
@endpush
