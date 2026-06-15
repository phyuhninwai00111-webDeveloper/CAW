@extends('layouts.app')

@section('title', 'Dashboard - Attendance')

@section('content')
    <div class="page-shell">
        <header class="hero">
            <div>
                <p class="eyebrow">Company Attendance</p>
                <p>Hello</p>
                <h1>Attendance Dashboard</h1>
                <p class="hero-copy">Track attendance activity at a glance, review the latest totals, and jump back into detailed records.</p>
            </div>
            <div class="hero-actions">
                <a href="{{ route('attendance') }}" class="btn btn-primary">View Attendance</a>
                <a href="{{ route('timesheets.index') }}" class="btn btn-primary">View Timesheets</a>
            </div>
        </header>

        <section class="stats-grid" id="summary" aria-live="polite">
            <article class="stat-card loading-card">
                <p>Total attendance records</p>
                <h2>Loading…</h2>
                <span>Updating in real time</span>
            </article>
            <article class="stat-card loading-card">
                <p>Late entries</p>
                <h2>Loading…</h2>
                <span>Needs review</span>
            </article>
        </section>

        <div style="margin-top: 24px;">
            <form id="logoutForm" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary">Logout</button>
            </form>
        </div>
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

function loadSummary(){
    $.getJSON('{{ route('dashboard.summary') }}')
        .done(function(res){
            if (res.error) {
                $('#summary').html('<article class="stat-card error-card"><p>Unable to load summary</p><h2>' + res.error + '</h2><span>Please try again.</span></article>');
                return;
            }
            renderSummary(res);
        })
        .fail(function(){
            $('#summary').html('<article class="stat-card error-card"><p>Unable to load summary</p><h2>Failed to load</h2><span>Please refresh the page.</span></article>');
        });
}

$(function(){
    loadSummary();
});
</script>
@endpush
