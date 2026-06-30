@extends('layouts.app')

@push('head')
  @include('components.datatables')
@endpush

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

      <div class="table-wrap">
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
                    <div style="display:inline-flex; align-items:center; gap:0.35rem;">
                      <a href="{{ route('timesheets.show', ['report_code' => $report->report_code, 'edit' => 1, 'detail_id' => $detail->id, 'mode' => 'edit']) }}#edit-timesheet" class="btn btn-sm" style="display:inline-flex; align-items:center; justify-content:center; min-width:2rem; min-height:2rem; background:#2f80ed; color:#fff; border:1px solid #1f6fd3; border-radius:0.65rem; box-shadow:0 4px 12px rgba(47,128,237,0.18); padding:0;" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                          <path d="M12.146.146a.5.5 0 0 1 .708 0l2.5 2.5a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-4 2a.5.5 0 0 1-.65-.65l2-4a.5.5 0 0 1 .11-.168zM11.293 2.5 13.5 4.707 14.793 3.5 12.586 1.293zM4.5 11.5 11.293 4.707 13.5 6.914 6.707 13.707z"/>
                        </svg>
                      </a>
                      <form method="POST" action="{{ route('timesheets.details.destroy', ['report_code' => $report->report_code, 'detail' => $detail->id]) }}" onsubmit="return confirm('Delete this detail row?');" style="margin:0;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm" style="display:inline-flex; align-items:center; justify-content:center; min-width:2rem; min-height:2rem; background:#eb5757; color:#fff; border:1px solid #d04444; border-radius:0.65rem; box-shadow:0 4px 12px rgba(235,87,87,0.18); padding:0;" title="Delete">
                          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 5h4a.5.5 0 0 1 .5.5V6h-5zM4 6.5A1.5 1.5 0 0 1 5.5 5h5A1.5 1.5 0 0 1 12 6.5V7h.5a.5.5 0 0 1 0 1h-.5v5a1.5 1.5 0 0 1-1.5 1.5h-4A1.5 1.5 0 0 1 4 13.5v-5H3.5a.5.5 0 0 1 0-1zm1.5 1.5v5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5v-5z"/>
                          </svg>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td class="empty-state">No detail rows have been added.</td>
                  @for ($i = 1; $i < 6; $i++)
                    <td></td>
                  @endfor
                </tr>
              @endforelse
            @empty
              <tr>
                <td class="empty-state">No timesheets available.</td>
                @for ($i = 1; $i < 6; $i++)
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
