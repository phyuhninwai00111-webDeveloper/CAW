@extends('layouts.app')

@section('title', 'Dashboard - Attendance')

@section('content')
    <div class="page-shell">
        <header class="hero">
            <div>
                <p class="eyebrow">Company Attendance</p>
                <h1>Attendance Dashboard</h1>
                <p class="hero-copy">Track attendance activity at a glance and review the latest totals.</p>
            </div>

        </header>

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
                        <h2>Attendance Trend</h2>
                        <p class="panel-copy">Daily total records and late entries.</p>
                    </div>
                </div>
                <canvas id="trendChart" height="260"></canvas>
            </article>
            <article class="panel chart-panel">
                <div class="panel-header">
                    <div>
                        <h2>Department Totals</h2>
                        <p class="panel-copy">Attendance volume grouped by department.</p>
                    </div>
                </div>
                <canvas id="departmentChart" height="260"></canvas>
            </article>
        </section>
    </div>
@endsection

@push('scripts')
<script>
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

function drawBarChart(canvasId, rows, valueKey, color) {
    var canvas = document.getElementById(canvasId);
    var ctx = canvas.getContext('2d');
    var width = canvas.clientWidth || canvas.parentElement.clientWidth;
    var height = Number(canvas.getAttribute('height')) || 260;
    var scale = window.devicePixelRatio || 1;

    canvas.width = width * scale;
    canvas.height = height * scale;
    ctx.setTransform(scale, 0, 0, scale, 0, 0);
    ctx.clearRect(0, 0, width, height);

    rows = rows || [];
    if (!rows.length) {
        ctx.fillStyle = '#cbd5e1';
        ctx.font = '14px Segoe UI, Arial';
        ctx.fillText('No data for the selected filters.', 18, 42);
        return;
    }

    var padding = 36;
    var chartWidth = width - padding * 2;
    var chartHeight = height - padding * 2;
    var max = Math.max.apply(null, rows.map(function(row) { return Number(row[valueKey] || 0); })) || 1;
    var barWidth = Math.max(12, chartWidth / rows.length - 10);

    ctx.strokeStyle = 'rgba(148, 163, 184, 0.28)';
    ctx.beginPath();
    ctx.moveTo(padding, padding);
    ctx.lineTo(padding, height - padding);
    ctx.lineTo(width - padding, height - padding);
    ctx.stroke();

    rows.forEach(function(row, index) {
        var value = Number(row[valueKey] || 0);
        var barHeight = (value / max) * chartHeight;
        var x = padding + index * (chartWidth / rows.length) + 5;
        var y = height - padding - barHeight;

        ctx.fillStyle = color;
        ctx.fillRect(x, y, barWidth, barHeight);
        ctx.fillStyle = '#e2e8f0';
        ctx.font = '12px Segoe UI, Arial';
        ctx.fillText(String(value), x, Math.max(18, y - 8));
    });
}

function drawTrendChart(rows) {
    drawBarChart('trendChart', rows, 'total', '#67e8f9');
}

function drawDepartmentChart(rows) {
    drawBarChart('departmentChart', rows, 'total', '#34d399');
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
