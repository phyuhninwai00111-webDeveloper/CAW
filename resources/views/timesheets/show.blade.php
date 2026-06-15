@extends('layouts.app')

@section('title', 'Timesheet Details - Attendance')

@section('content')
  <div class="page-shell">
    <div class="attendance-page-actions">
      <a href="{{ route('timesheets.index') }}" class="btn btn-secondary page-back-btn">Back to Timesheets</a>
      <a href="#" id="logout" class="btn btn-secondary page-logout-btn">Logout</a>
    </div>

    <header class="hero compact-hero">
      <div>
        <p class="eyebrow">Timesheet Detail</p>
        <h1>{{ $report->report_code }}</h1>
        <p class="hero-copy">{{ $report->employee->name }} — {{ $report->report_date->format('Y-m-d') }}</p>
      </div>
      <div class="hero-actions">
        <a href="{{ route('attendance') }}" class="btn btn-primary">View Attendance</a>
      </div>
    </header>

    <section class="panel filter-panel">
      <div class="panel-header">
        <div>
          <h2>Timesheet Information</h2>
          <p class="panel-copy">{{ $report->employee->department->department_name ?? 'No department' }}</p>
        </div>
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
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {{-- ဒီ code က data ဘယ်နှစ်ခုရှိလဲဆိုတာ ပေါ်ပေးပါလိမ့်မယ် --}}
<p>Total projects: {{ $report->details->count() }}</p>
            @foreach($report->details as $detail)
              <tr>
                <td>{{ \Carbon\Carbon::parse($report->report_date)->format('Y-m-d') }}</td>
                <td>{{ $detail->project_name }}</td>
                <td>{{ $detail->functions }}</td>
                <td>{{ $detail->status }}</td>
                <td>{{ $detail->remark }}</td>
                <td>
            {{-- Edit ခလုတ်ကို နှိပ်ရင် Form ထဲက ID ကို ဖြည့်ပေးမယ် --}}
            <button type="button" class="btn btn-primary"
                onclick="
                    document.getElementById('edit_project_name').value = '{{ addslashes($detail->project_name) }}';
                    document.getElementById('edit_functions').value = '{{ addslashes($detail->functions) }}';
                    document.getElementById('edit_status').value = '{{ $detail->status }}';
                    document.getElementById('edit_remark').value = '{{ addslashes($detail->remark) }}';
                    // Form ရဲ့ Action ကို ဒီ detail ရဲ့ ID နဲ့ ချက်ချင်းပြောင်းပေးမယ်
                    document.getElementById('editForm').action = '/timesheets/detail/{{ $detail->id }}';
                ">
                Edit
            </button>
        </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>

    @if($canEdit)
      <section class="panel filter-panel">
        <div class="panel-header">
          <div>
            <h2>Edit Timesheet</h2>
            <p class="panel-copy">Update the selected timesheet entry.</p>
          </div>
        </div>

        {{-- <form id="editForm" method="POST" action="{{ route('timesheets.update', $report->report_code) }}" class="filter-form">
          @csrf
          @method('PATCH')

          <label>
            <span>Project Name</span>
            <textarea name="project_name" rows="2" required>{{ old('project_name', $report->details->first()?->project_name) }}</textarea>
            @error('project_name')<span class="error-message">{{ $message }}</span>@enderror
          </label>
          <label>
            <span>Functions</span>
            <textarea name="functions" rows="2" required>{{ old('functions', $report->details->first()?->functions) }}</textarea>
            @error('functions')<span class="error-message">{{ $message }}</span>@enderror
          </label>
          <label>
            <span>Status</span>
            <select name="status" required>
              <option value="Pending"{{ old('status', $report->details->first()?->status) === 'Pending' ? ' selected' : '' }}>Pending</option>
              <option value="Done"{{ old('status', $report->details->first()?->status) === 'Done' ? ' selected' : '' }}>Done</option>
            </select>
            @error('status')<span class="error-message">{{ $message }}</span>@enderror
          </label>
          <label>
            <span>Remark</span>
            <textarea name="remark" rows="2" required>{{ old('remark', $report->details->first()?->remark) }}</textarea>
            @error('remark')<span class="error-message">{{ $message }}</span>@enderror
          </label>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </form> --}}
        <form id="editForm" method="POST" action="" class="filter-form">
    @csrf
    @method('PUT')

    <input type="text" name="project_name" id="edit_project_name" required>
    <input type="text" name="functions" id="edit_functions" required>
    <select name="status" id="edit_status">
        <option value="Done">Done</option>
        <option value="Pending">Pending</option>
    </select>
    <textarea name="remark" id="edit_remark"></textarea>

    <button type="submit"class="btn btn-primary">Save Changes</button>
</form>
      </section>
    @endif
  </div>
@endsection
