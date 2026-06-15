@extends('layouts.app')

@section('title', 'Attendance - Attendance')

@section('content')
  <div class="page-shell">
    <div class="attendance-page-actions">
      <a href="{{ route('dashboard') }}" class="btn btn-secondary page-back-btn">Back to Dashboard</a>
      <a href="#" id="logout" class="btn btn-secondary page-logout-btn">Logout</a>
    </div>

    <header class="hero compact-hero">
      <div>
        <p class="eyebrow">Attendance</p>
        <h1>Attendance Records</h1>
        <p class="hero-copy" id="attendance-filter-copy">Filter by date range.</p>
      </div>
      <div class="hero-actions">
        <button type="button" id="btn-checkin" class="btn btn-primary">Check In</button>
        <button type="button" id="btn-checkout" class="btn btn-primary">Check Out</button>
      </div>
    </header>

    <section class="panel filter-panel">
      <div class="panel-header">
        <div>
          <h2>Filter records</h2>
          <p class="panel-copy" id="filter-help">Choose a date range to narrow the attendance list.</p>
        </div>
      </div>

      <form id="filters" class="filter-form">
        <label>
          <span>From</span>
          <input type="date" name="from" id="date-from">
        </label>
        <label>
          <span>To</span>
          <input type="date" name="to" id="date-to">
        </label>
        <span id="department-filter-slot"></span>
        <span id="employee-code-filter-slot"></span>
        <button type="submit" class="btn btn-primary">Apply Filter</button>
      </form>
    </section>

    <section class="panel table-panel">
      <div class="panel-header">
        <h2>Records</h2>
      </div>
      <div class="table-wrap">
        <table id="tbl" class="attendance-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Date</th>
              <th>Check In</th>
              <th>Check Out</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </section>
  </div>
@endsection

@push('scripts')
<script>
function isHrRole(roleId) {
  return Number(roleId) === 1;
}

function tableColumnCount(roleId) {
  return isHrRole(roleId) ? 6 : 5;
}

var timesheetUrlBase = '{{ url('/timesheets') }}';

function renderTableHeader(roleId) {
  var html = '<tr>' +
    '<th>Name</th>' +
    '<th>Date</th>' +
    '<th>Check In</th>' +
    '<th>Check Out</th>' +
    '<th>Report</th>';

  if (isHrRole(roleId)) {
    html += '<th>Department</th>';
  }

  html += '</tr>';
  $('#tbl thead').html(html);
}

function escapeHtml(value) {
  return String(value || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function isLateCheckIn(checkIn) {
  return String(checkIn || '') > '09:00:00';
}

function loadDepartments() {
  if (window.departmentsLoaded || !$('#department_id').length) {
    return;
  }

  $.getJSON('{{ route('departments.index') }}')
    .done(function(res) {
      var html = '<option value="">-- Select Department --</option>';
      (res.departments || []).forEach(function(department) {
        html += '<option value="' + department.id + '">' + escapeHtml(department.department_name) + '</option>';
      });
      $('#department_id').html(html);
      window.departmentsLoaded = true;
    })
    .fail(function() {
      $('#department_id').html('<option value="">Unable to load departments</option>');
    });
}

function renderRows(rows, roleId){
  var html = '';
  var isHr = isHrRole(roleId);
  var colspan = tableColumnCount(roleId);

  if (!rows.length) {
    html = '<tr><td colspan="' + colspan + '" class="empty-state">No attendance records found for the selected range.</td></tr>';
    $('#tbl tbody').html(html);
    return;
  }

  rows.forEach(function(r){
    var checkInClass = isLateCheckIn(r.check_in) ? ' class="late-check-in"' : '';

    html += '<tr>' +
      '<td>' + escapeHtml(r.name) + '</td>' +
      '<td>' + escapeHtml(r.attendance_date) + '</td>' +
      '<td' + checkInClass + '>' + escapeHtml(r.check_in) + '</td>' +
      '<td>' + escapeHtml(r.check_out) + '</td>' +
      '<td>' + (r.report_code ? '<a href="' + timesheetUrlBase + '/' + encodeURIComponent(r.report_code) + '">' + escapeHtml(r.report_code) + '</a>' : '') + '</td>';
//'<td>' + (r.report_code ? '<a href="' + timesheetUrlBase + '/' + encodeURIComponent(r.report_code) + '">' + escapeHtml(r.report_code) + '</a>' : '-') + '</td>' +
    if (isHr) {
      html += '<td>' + escapeHtml(r.department_name) + '</td>';
    }

    html += '</tr>';
  });

  $('#tbl tbody').html(html);
}

function getRequestError(xhr, fallback) {
  if (xhr.responseJSON && xhr.responseJSON.error) {
    return xhr.responseJSON.error;
  }

  return fallback;
}

function applyRoleControls(roleId) {
  var isHr = isHrRole(roleId);
  var canSearchEmployeeCode = isHr || Number(roleId) === 2;

  if (isHr && !$('#department-filter').length) {
    $('#department-filter-slot').replaceWith(
      '<label id="department-filter">' +
        '<span>Department</span>' +
        '<select name="department_id" id="department_id">' +
          '<option value="">Loading departments...</option>' +
        '</select>' +
      '</label>'
    );
    loadDepartments();
  } else if (!isHr && $('#department-filter').length) {
    $('#department-filter').replaceWith('<span id="department-filter-slot"></span>');
  }

  if (canSearchEmployeeCode && !$('#employee-code-filter').length) {
    $('#employee-code-filter-slot').replaceWith(
      '<label id="employee-code-filter">' +
        '<span>Employee Code</span>' +
        '<input type="text" name="employee_code" placeholder="Employee code">' +
      '</label>'
    );
  } else if (!canSearchEmployeeCode && $('#employee-code-filter').length) {
    $('#employee-code-filter').replaceWith('<span id="employee-code-filter-slot"></span>');
  }

  $('#attendance-filter-copy').text(isHr ? 'Filter by date range, department, and employee code.' : (canSearchEmployeeCode ? 'Filter by date range and employee code.' : 'Filter by date range.'));
  $('#filter-help').text(isHr ? 'Choose a date range, department, or employee code to narrow the attendance list.' : (canSearchEmployeeCode ? 'Choose a date range or employee code to narrow the attendance list.' : 'Choose a date range to narrow the attendance list.'));
}

function load(filters){
  $.getJSON('{{ route('attendance.data') }}', filters)
    .done(function(res){
      if (res.error) {
        $('#tbl tbody').html('<tr><td colspan="' + tableColumnCount(res.role_id) + '" class="empty-state">' + res.error + '</td></tr>');
        return;
      }

      applyRoleControls(res.role_id);
      renderTableHeader(res.role_id);
      renderRows(res.data || [], res.role_id);
    })
    .fail(function(){
      $('#tbl tbody').html('<tr><td colspan="5" class="empty-state">Failed to load attendance records.</td></tr>');
    });
}

function formatDateInput(date) {
  var month = String(date.getMonth() + 1).padStart(2, '0');
  var day = String(date.getDate()).padStart(2, '0');
  return date.getFullYear() + '-' + month + '-' + day;
}

function setDefaultDateRange() {
  var today = new Date();
  var fromDate = new Date(today);
  fromDate.setDate(today.getDate() - 30);

  $('#date-from').val(formatDateInput(fromDate));
  $('#date-to').val(formatDateInput(today));
}

function loadCurrentFilters() {
  load($('#filters').serialize());
}

$('#filters').on('submit', function(e){
  e.preventDefault();
  loadCurrentFilters();
});

function submitAttendanceAction(url, title, successMessage) {
  appConfirm('Confirm ' + title.toLowerCase() + ' now?', {
    title: title,
    confirmText: title
  }).then(function(confirmed) {
    if (!confirmed) return;

    $.ajax({
      url: url,
      method: 'POST',
      dataType: 'json',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    }).done(function(res){
      if (res.error) {
        appAlert(res.error, 'error');
        return;
      }
      appAlert(successMessage + ' at ' + (res.check_in || res.check_out), 'success');
      loadCurrentFilters();
    }).fail(function(xhr){
      appAlert(getRequestError(xhr, 'Failed to perform ' + title.toLowerCase() + '.'), 'error');
    });
  });
}

$(function(){
  setDefaultDateRange();
  loadCurrentFilters();

  $('#btn-checkin').on('click', function(e){
    e.preventDefault();
    submitAttendanceAction('{{ route('attendance.checkin') }}', 'Check in', 'Checked in');
  });

  $('#btn-checkout').on('click', function(e){
    e.preventDefault();
    submitAttendanceAction('{{ route('attendance.checkout') }}', 'Check out', 'Checked out');
  });

  $('#logout').on('click', function(e){
    e.preventDefault();
    $.ajax({
      url: '{{ route('logout') }}',
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      success: function(){ window.location.href = '{{ route('login') }}'; },
      error: function(){ appAlert('Logout failed', 'error'); }
    });
  });
});v
</script>
@endpush
