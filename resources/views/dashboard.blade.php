@extends('layouts.app')

@section('title', 'Dashboard - Attendance')

@section('content')
    <div class="page-shell">
        <header class="hero">
            <div>
                <p class="eyebrow">Company Attendance</p>
                <h1>Attendance Dashboard</h1>
                <p class="hero-copy" id="dashboard-copy">
                    @if ($isHr)
                        Review company-wide attendance totals and late entries across all departments.
                    @elseif ($isHod)
                        Review attendance totals and late entries for your department.
                    @else
                        Review your personal attendance totals and late entries.
                    @endif
                </p>
            </div>
        </header>

        <section class="panel filter-panel">
            <div class="panel-header">
                <div>
                    <h2>Filter summary</h2>
                    <p class="panel-copy" id="dashboard-filter-help">
                        @if ($isHr)
                            Choose a date range, department, or employee code to narrow the dashboard.
                        @elseif ($isHod)
                            Choose a date range or employee code to narrow the dashboard.
                        @else
                            Choose a date range to narrow the dashboard.
                        @endif
                    </p>
                </div>
            </div>

            <form id="dashboardFilters" class="filter-form">
                <label>
                    <span>From</span>
                    <input type="date" name="from" id="dashboard-from">
                </label>
                <label>
                    <span>To</span>
                    <input type="date" name="to" id="dashboard-to">
                </label>
                @if ($isHr)
                    <label>
                        <span>Department</span>
                        <select name="department_id" id="dashboard-department">
                            <option value="">-- Select department --</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
                @if ($isHr || $isHod)
                    <label>
                        <span>Employee Code</span>
                        <input type="text" name="employee_code" id="dashboard-employee-code" placeholder="Employee code">
                    </label>
                @endif
                <button type="submit" class="btn btn-primary">Apply Filter</button>
            </form>
        </section>

        <section class="stats-grid" id="summary" aria-live="polite">
            <article class="stat-card loading-card">
                <p>Total attendance records</p>
                <h2>Loading...</h2>
                <span>Updating in real time</span>
            </article>
            <article class="stat-card loading-card">
                <p>Late entries</p>
                <h2>Loading...</h2>
                <span>Needs review</span>
            </article>
        </section>

        <section class="dashboard-chart-grid">
            <article class="panel chart-panel">
                <div class="panel-header">
                    <div>
                        <h2>Attendance Breakdown</h2>
                        <p class="panel-copy">On-time vs late entries for the selected range.</p>
                    </div>
                </div>
                <canvas id="trendChart" height="260"></canvas>
            </article>
            @if ($isHr || $isHod)
                <article class="panel chart-panel" id="department-chart-panel">
                    <div class="panel-header">
                        <div>
                            <h2>Department Totals</h2>
                            <p class="panel-copy">
                                @if ($isHr)
                                    Attendance volume grouped by department.
                                @else
                                    Attendance volume within your department.
                                @endif
                            </p>
                        </div>
                    </div>
                    <canvas id="departmentChart" height="260"></canvas>
                </article>
            @endif
        </section>
    </div>
@endsection

@push('scripts')
<script>
var dashboardRoleId = {{ (int) $roleId }};

function renderSummary(res) {
    var total = Number(res.total || 0);
    var late = Number(res.late || 0);

    $('#summary').html(
        '<article class="stat-card">' +
            '<p>Total attendance records</p>' +
            '<h2>' + total.toLocaleString() + '</h2>' +
            '<span>' + (total === 1 ? 'record logged' : 'records logged') + '</span>' +
        '</article>' +
        '<article class="stat-card">' +
            '<p>Late entries</p>' +
            '<h2>' + late.toLocaleString() + '</h2>' +
            '<span>' + (late === 1 ? 'entry flagged' : 'entries flagged') + '</span>' +
        '</article>'
    );
}

function formatDateInput(date) {
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var day = String(date.getDate()).padStart(2, '0');
    return date.getFullYear() + '-' + month + '-' + day;
}

function setDefaultDashboardRange() {
    var today = new Date();
    var fromDate = new Date(today);
    fromDate.setDate(today.getDate() - 30);
    $('#dashboard-from').val(formatDateInput(fromDate));
    $('#dashboard-to').val(formatDateInput(today));
}

function drawPieChart(canvasId, slices) {
    var canvas = document.getElementById(canvasId);
    if (!canvas) {
        return;
    }

    var ctx = canvas.getContext('2d');
    var width = canvas.clientWidth || canvas.parentElement.clientWidth;
    var height = Number(canvas.getAttribute('height')) || 260;
    var scale = window.devicePixelRatio || 1;

    canvas.width = width * scale;
    canvas.height = height * scale;
    ctx.setTransform(scale, 0, 0, scale, 0, 0);
    ctx.clearRect(0, 0, width, height);

    slices = (slices || []).filter(function(slice) {
        return Number(slice.value || 0) > 0;
    });

    if (!slices.length) {
        ctx.fillStyle = '#cbd5e1';
        ctx.font = '14px Segoe UI, Arial';
        ctx.fillText('No data for the selected filters.', 18, 42);
        return;
    }

    var total = slices.reduce(function(sum, slice) {
        return sum + Number(slice.value || 0);
    }, 0);

    var paddingLeft = 18;
    var paddingRight = 16;
    var paddingTop = 22;
    var paddingBottom = 22;
    var legendGap = 20;
    var legendSwatch = 12;
    var legendSwatchGap = 8;
    var legendLineHeight = 22;

    ctx.font = '12px Segoe UI, Arial';

    var legendItems = slices.map(function(slice) {
        var value = Number(slice.value || 0);
        var percent = Math.round((value / total) * 100);
        var text = slice.label + ' (' + value.toLocaleString() + ', ' + percent + '%)';

        return {
            color: slice.color,
            text: text,
            textWidth: ctx.measureText(text).width
        };
    });

    var maxTextWidth = Math.max.apply(null, legendItems.map(function(item) {
        return item.textWidth;
    }).concat([0]));
    var legendWidth = legendSwatch + legendSwatchGap + maxTextWidth;
    var legendBlockHeight = legendItems.length * legendLineHeight;
    var legendX = Math.max(paddingLeft + 80, width - paddingRight - legendWidth);
    var pieZoneRight = legendX - legendGap;
    var pieZoneWidth = pieZoneRight - paddingLeft;
    var pieZoneHeight = height - paddingTop - paddingBottom;

    var radius = Math.min(pieZoneWidth * 0.44, pieZoneHeight * 0.46, 84);
    radius = Math.max(radius, 34);

    var centerX = paddingLeft + radius + 4;
    if (centerX + radius + legendGap > legendX) {
        radius = Math.max(30, (legendX - legendGap - paddingLeft - 4) / 2);
        centerX = paddingLeft + radius + 4;
    }

    var centerY = paddingTop + pieZoneHeight / 2;
    var startAngle = -Math.PI / 2;

    slices.forEach(function(slice) {
        var value = Number(slice.value || 0);
        var sliceAngle = (value / total) * Math.PI * 2;
        var endAngle = startAngle + sliceAngle;

        ctx.fillStyle = slice.color;
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.arc(centerX, centerY, radius, startAngle, endAngle);
        ctx.closePath();
        ctx.fill();

        if (sliceAngle > 0.18) {
            var midAngle = startAngle + sliceAngle / 2;
            var labelX = centerX + Math.cos(midAngle) * (radius * 0.62);
            var labelY = centerY + Math.sin(midAngle) * (radius * 0.62);
            var percent = Math.round((value / total) * 100);

            ctx.fillStyle = '#0f172a';
            ctx.font = 'bold 12px Segoe UI, Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(percent + '%', labelX, labelY);
        }

        startAngle = endAngle;
    });

    ctx.strokeStyle = '#0f172a';
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
    ctx.stroke();

    var legendY = centerY - legendBlockHeight / 2 + legendLineHeight - 2;
    legendY = Math.max(paddingTop + legendLineHeight, legendY);
    legendY = Math.min(legendY, height - paddingBottom - (legendItems.length - 1) * legendLineHeight);

    ctx.textAlign = 'left';
    ctx.textBaseline = 'alphabetic';
    ctx.font = '12px Segoe UI, Arial';

    legendItems.forEach(function(item) {
        ctx.fillStyle = item.color;
        ctx.fillRect(legendX, legendY - 10, legendSwatch, legendSwatch);

        ctx.fillStyle = '#e2e8f0';
        ctx.fillText(item.text, legendX + legendSwatch + legendSwatchGap, legendY);

        legendY += legendLineHeight;
    });
}

function drawTrendChart(rows) {
    var total = 0;
    var late = 0;

    (rows || []).forEach(function(row) {
        total += Number(row.total || 0);
        late += Number(row.late || 0);
    });

    drawPieChart('trendChart', [
        { label: 'On time', value: Math.max(0, total - late), color: '#67e8f9' },
        { label: 'Late entries', value: late, color: '#fb7185' }
    ]);
}

function drawDepartmentChart(rows) {
    var colors = ['#67e8f9', '#34d399', '#fb7185', '#a78bfa', '#fbbf24', '#60a5fa', '#f472b6'];

    drawPieChart('departmentChart', (rows || []).map(function(row, index) {
        return {
            label: row.label || 'Unknown',
            value: Number(row.total || 0),
            color: colors[index % colors.length]
        };
    }));
}

function loadSummary(){
    $.getJSON('{{ route('dashboard.summary') }}', $('#dashboardFilters').serialize())
        .done(function(res){
            if (res.error) {
                $('#summary').html('<article class="stat-card error-card"><p>Unable to load summary</p><h2>' + res.error + '</h2><span>Please try again.</span></article>');
                return;
            }
            renderSummary(res);
            drawTrendChart(res.byDate || []);
            drawDepartmentChart(res.byDepartment || []);
        })
        .fail(function(){
            $('#summary').html('<article class="stat-card error-card"><p>Unable to load summary</p><h2>Failed to load</h2><span>Please refresh the page.</span></article>');
        });
}

$(function(){
    setDefaultDashboardRange();
    loadSummary();

    $('#dashboardFilters').on('submit', function(e) {
        e.preventDefault();
        loadSummary();
    });

    $(window).on('resize', loadSummary);
});
</script>
@endpush