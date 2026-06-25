@extends('layouts.app')

@push('head')
  @include('components.datatables')
@endpush
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
{{-- do you think this link is required --}}
{{-- this code is mine --}}
<style>
  .dataTables_paginate {
    display: flex !important;
    justify-content: flex-end !important;
    align-items: center !important;
    gap: 5px !important;
    margin-top: 15px !important;
  }
  .dataTables_paginate .paginate_button {
    padding: 5px 10px !important;
    cursor: pointer !important;
    color: #fff !important; /* စာသားအရောင် */
  }
  .dataTables_filter {
    margin-bottom: 15px !important;
  }
</style>
@section('title', 'Attendance - Attendance')

@section('content')
  <div class="page-shell">
    <!-- <div class="attendance-page-actions">
      <a href="{{ route('dashboard') }}" class="btn btn-secondary page-back-btn">Back to Dashboard</a>
      <a href="#" id="logout" class="btn btn-secondary page-logout-btn">Logout</a>
    </div> -->

    <header class="hero compact-hero">
      <div>
        <p class="eyebrow">Attendance</p>
        <h1>Attendance Records</h1>

        <!-- <div style="display: flex;margin-left:auto;">
          <span id="current-time-display" style="color: #63e2b7; font-weight: bold; font-size: 1.1rem; margin-right: 10px;"></span>
        </div> -->
        <!-- <p class="hero-copy" id="attendance-filter-copy">Filter by date range.</p> -->
         <p>Current Time:
          <span id="current-time-display" style="color:rgb(228, 240, 236); font-weight: bold; font-size: 1.1rem;"></span>
        </p>
      </div>

      <div class="hero-actions">
      <!-- /btn-secondary SS -->
      @if(auth()->user()->role_id === 1 || auth()->user()->role_id === 2)
        <button type="button" id="btn-my-history" class="btn btn-primary">My History</button>
        <button type="button" id="btn-all-records" class="btn btn-primary">All Records</button>
      @endif
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

      <!-- <form id="filters" class="filter-form">
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
    </section> -->
      <form id="filters" class="filter-form" style="display: flex !important; flex-direction: row !important; flex-wrap: wrap !important; gap: 15px !important; align-items: flex-end !important; justify-content: flex-start !important; width: 100% !important;">

        <label style="width: 170px !important; flex: none !important; display: flex !important; flex-direction: column !important;">
          <span>From</span>
          <input type="date" name="from" id="date-from" style="width: 100% !important; height: 40px !important; box-sizing: border-box !important; padding: 6px 10px !important;">
        </label>

        <label style="width: 170px !important; flex: none !important; display: flex !important; flex-direction: column !important;">
          <span>To</span>
          <input type="date" name="to" id="date-to" style="width: 100% !important; height: 40px !important; box-sizing: border-box !important; padding: 6px 10px !important;">
        </label>

        <span id="department-filter-slot" style="display: none !important;">Department</span>
        <span id="employee-code-filter-slot" style="display: none !important;">Employee Code</span>

        <button type="submit" class="btn btn-primary" style="height: 40px !important; padding: 0 20px !important; white-space: nowrap !important; margin-bottom: 2px !important; flex: none !important;">Apply Filter</button>

      </form>
    </section>
    <section class="panel table-panel" id="records-section">
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
          <tbody>

          </tbody>
        </table>
      </div>
    </section>
  </div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>



<script>
function isHrRole(roleId) {
  return Number(roleId) === 1;
}

function tableColumnCount(roleId) {
  return isHrRole(roleId) ? 5 : 4;//6:5
}

var timesheetUrlBase = '{{ url('/timesheets') }}';
var attendanceScope = 'all';

function renderTableHeader(roleId) {
  var html = '<tr>' +
    '<th>Name</th>' +
    '<th>Date</th>' +
    '<th>Check In</th>' +
    '<th>Check Out</th>' ;


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
  return String(checkIn || '') > '10:00:00';
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
      '<td>' + escapeHtml(r.check_out) + '</td>';
      //'<td>' + (r.report_code ? '<a href="' + timesheetUrlBase + '/' + encodeURIComponent(r.report_code) + '">' + escapeHtml(r.report_code) + '</a>' : '') + '</td>';
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

/*function applyRoleControls(roleId) {
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
*/
function applyRoleControls(roleId) {
  var isHr = isHrRole(roleId);
  var canSearchEmployeeCode = isHr || Number(roleId) === 2;

  // Department handling
  if (isHr) {
    if (!$('#department-filter').length) {
      $('#department-filter-slot').replaceWith(
        '<label id="department-filter" style="width: 200px !important; flex: none !important; display: flex !important; flex-direction: column !important;">' +
          '<span>Department</span>' +
          '<select name="department_id" id="department_id" style="width: 100% !important; height: 40px !important; box-sizing: border-box !important;">' +
            '<option value="">Loading departments...</option>' +
          '</select>' +
        '</label>'
      );
      loadDepartments();
    } else {
      $('#department-filter').attr('style', 'width: 200px !important; flex: none !important; display: flex !important; flex-direction: column !important;').show();
    }
  } else {
    if ($('#department-filter').length) {
      $('#department-filter').replaceWith('<span id="department-filter-slot" style="display:none !important;"></span>');
    } else {
      $('#department-filter-slot').hide();
    }
  }

  // Employee Code handling
  if (canSearchEmployeeCode) {
    if (!$('#employee-code-filter').length) {
      $('#employee-code-filter-slot').replaceWith(
        '<label id="employee-code-filter" style="width: 170px !important; flex: none !important; display: flex !important; flex-direction: column !important;">' +
          '<span>Employee Code</span>' +
          '<input type="text" name="employee_code" placeholder="Employee code" style="width: 100% !important; height: 40px !important; box-sizing: border-box !important;">' +
        '</label>'
      );
    } else {
      $('#employee-code-filter').attr('style', 'width: 170px !important; flex: none !important; display: flex !important; flex-direction: column !important;').show();
    }
  } else {
    if ($('#employee-code-filter').length) {
      $('#employee-code-filter').replaceWith('<span id="employee-code-filter-slot" style="display:none !important;"></span>');
    } else {
      $('#employee-code-filter-slot').hide();
    }
  }

  $('#attendance-filter-copy').text(isHr ? 'Filter by date range, department, and employee code.' : (canSearchEmployeeCode ? 'Filter by date range and employee code.' : 'Filter by date range.'));
  $('#filter-help').text(isHr ? 'Choose a date range, department, or employee code to narrow the attendance list.' : (canSearchEmployeeCode ? 'Choose a date range or employee code to narrow the attendance list.' : 'Choose a date range to narrow the attendance list.'));
}
/*function load(filters){
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
*/
/*function load(filters){
  $.getJSON('{{ route('attendance.data') }}', filters)
    .done(function(res){
      if (res.error) {
        $('#tbl tbody').html('<tr><td colspan="' + tableColumnCount(res.role_id) + '" class="empty-state">' + res.error + '</td></tr>');
        return;
      }

      if ($.fn.dataTable.isDataTable('#tbl')) {
        $('#tbl').DataTable().destroy();
      }

      applyRoleControls(res.role_id);
      renderTableHeader(res.role_id);
      renderRows(res.data || [], res.role_id);

      $('#tbl').DataTable({
        pageLength: 10,
        lengthChange: false,
        pagingType: 'simple',
        ordering: false,
        searching: false,
        destroy: true
      });
    })
    .fail(function(){
      $('#tbl tbody').html('<tr><td colspan="5" class="empty-state">Failed to load attendance records.</td></tr>');
    });
}
    */



function load(filters){
 $.getJSON('{{ route('attendance.data') }}', filters)
    .done(function(res){
      if (res.error) {
        $('#tbl tbody').html('<tr><td colspan="' + tableColumnCount(res.role_id) + '" class="empty-state">' + res.error + '</td></tr>');
        return;
      }

      // ၁။ လက်ရှိ ရှိနေပြီးသား DataTable ကို ဖျက်ပစ်ပါ (ရှိခဲ့လျှင်)
      if ($.fn.DataTable.isDataTable('#tbl')) {
        $('#tbl').DataTable().destroy();
      }

      applyRoleControls(res.role_id);
      renderTableHeader(res.role_id);
      renderRows(res.data || [], res.role_id);

      // ၂။ Dynamic Columns Array ကို တည်ဆောက်ခြင်း
      // ဝင်လာတဲ့ Role အလိုက် ကော်လံအရေအတွက်ကို ညှိပေးရန်
      var dtColumns = [
        { orderable: false }, // Name
        { orderable: false }, // Date
        { orderable: false }, // Check In
        { orderable: false }  // Check Out
      ];

      // အကယ်၍ HR Role ဖြစ်လို့ Table Header မှာ Department ပါလာခဲ့ရင်
      // DataTable ကိုလည်း ၅ ခုမြောက် Column ရှိတယ်လို့ အသိပေးရပါမယ်
      if (isHrRole(res.role_id)) {
        dtColumns.push({ orderable: false }); // Department
      }

      // ၃။ Table ထဲ data ရောက်သွားပြီဖြစ်လို့ DataTable ကို ပုံစံအသစ်ဖြင့် စတင်သက်ဝင်စေပါမည်
      $('#tbl').DataTable({
        "pageLength": 10,
        "lengthChange": false,
        "pagingType": "simple",
        "ordering": false,
        "searching": false,
        "destroy": true,
        "columns": dtColumns // ⬅️ ဤစာကြောင်းကို ထည့်သွင်းပေးရပါမည်။
      });
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
  function startLiveClock() {
    function updateClock() {
      var now = new Date();
      var hours = now.getHours();
      var minutes = now.getMinutes();
      var seconds = now.getSeconds();

      // 1. AM သို့မဟုတ် PM ခွဲခြားခြင်း
      var period = hours >= 12 ? 'PM' : 'AM';

      // 2. 12-hour format ပြောင်းခြင်း
      hours = hours % 12;
      hours = hours ? hours : 12; // 0 ဖြစ်နေရင် 12 လို့ပြမယ်

      // 3. နာရီ၊ မိနစ်၊ စက္ကန့် အားလုံးကို ဂဏန်းနှစ်လုံးတွဲ ဖြစ်အောင်လုပ်ခြင်း (ဥပမာ- 09:05:55)
      hours = String(hours).padStart(2, '0'); // ဒီစာကြောင်း ထည့်လိုက်လို့ 9 ကနေ 09 ဖြစ်သွားပါပြီ
      minutes = String(minutes).padStart(2, '0');
      seconds = String(seconds).padStart(2, '0');

      // 4. ပုံစံစုစည်းခြင်း
      var timeString = hours + ':' + minutes + ':' + seconds;

      // 5. UI ပေါ်တွင် "09:05:55 AM" ပုံစံအတိုင်း စာသား ထုတ်ပြခြင်း
      $('#current-time-display').text(timeString + ' ' + period);
    }

    updateClock(); // ချက်ချင်း တစ်ကြိမ် Run မယ်
    setInterval(updateClock, 1000); // ၁ စက္ကန့်လျှင် တစ်ကြိမ် ပုံမှန် Update လုပ်ပေးမယ်
}

// Clock ကို စတင်မောင်းနှင်ပါ
startLiveClock();
}

function loadCurrentFilters() {
  var filters = $('#filters').serialize();
  filters += (filters ? '&' : '') + 'scope=' + encodeURIComponent(attendanceScope);
  load(filters);
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

  // $('#btn-my-history').on('click', function(e){
  //   e.preventDefault();
  //   attendanceScope = 'my';
  //   loadCurrentFilters();
  // });
  $('#btn-my-history').on('click', function(e){
  e.preventDefault();
  attendanceScope = 'my';
  loadCurrentFilters();

  setTimeout(function () {
      scrollToRecords();
  }, 300);
});

  // $('#btn-all-records').on('click', function(e){
  //   e.preventDefault();
  //   attendanceScope = 'all';
  //   loadCurrentFilters();
  // });
  $('#btn-all-records').on('click', function(e){
  e.preventDefault();
  attendanceScope = 'all';
  loadCurrentFilters();

  setTimeout(function () {
      scrollToRecords();
  }, 300);
});
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

  function scrollToRecords() {
    $('html, body').animate({
        scrollTop: $('#records-section').offset().top - 20
    }, 500);
}
});
</script>
@endpush
