@php
    $isEdit = filled($timesheetId ?? null);
    $formAction = $isEdit ? route('timesheets.update', $timesheetId) : route('timesheets.store');
    $formDetails = old('details');

    if (! $formDetails) {
        $formDetails = isset($report)
            ? $report->details->map(fn ($detail) => [
                'project_name' => $detail->project_name,
                'functions' => $detail->functions,
                'status' => $detail->status,
                'remark' => $detail->remark,
            ])->values()->all()
            : [[
                'project_name' => '',
                'functions' => '',
                'status' => 'Pending',
                'remark' => '',
            ]];
    }
@endphp

<form method="POST" action="{{ $formAction }}" class="timesheet-form" data-timesheet-form>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="timesheet-meta-grid">
        <label>
            <span>Report Code</span>
            <input type="text" name="report_code" value="{{ old('report_code', $report->report_code ?? $displayCode) }}" readonly required>
            @error('report_code')<span class="error-message">{{ $message }}</span>@enderror
        </label>
        <label>
            <span>Date</span>
            <input type="date" name="report_date" value="{{ old('report_date', isset($report) ? $report->report_date->format('Y-m-d') : now()->toDateString()) }}" required>
            @error('report_date')<span class="error-message">{{ $message }}</span>@enderror
        </label>
    </div>

    <div class="timesheet-rows" data-timesheet-rows>
        @foreach($formDetails as $index => $detail)
            <div class="timesheet-row" data-timesheet-row>
                <label>
                    <span>Project Name</span>
                    <textarea name="details[{{ $index }}][project_name]" data-name="project_name" rows="2" required>{{ $detail['project_name'] ?? '' }}</textarea>
                    @error("details.$index.project_name")<span class="error-message">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span>Functions</span>
                    <textarea name="details[{{ $index }}][functions]" data-name="functions" rows="2" required>{{ $detail['functions'] ?? '' }}</textarea>
                    @error("details.$index.functions")<span class="error-message">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span>Status</span>
                    <select name="details[{{ $index }}][status]" data-name="status" required>
                        <option value="Pending" @selected(($detail['status'] ?? 'Pending') === 'Pending')>Pending</option>
                        <option value="Done" @selected(($detail['status'] ?? '') === 'Done')>Done</option>
                    </select>
                    @error("details.$index.status")<span class="error-message">{{ $message }}</span>@enderror
                </label>
                <label>
                    <span>Remark</span>
                    <textarea name="details[{{ $index }}][remark]" data-name="remark" rows="2">{{ $detail['remark'] ?? '' }}</textarea>
                    @error("details.$index.remark")<span class="error-message">{{ $message }}</span>@enderror
                </label>
                <button type="button" class="btn btn-secondary timesheet-remove-row" data-remove-row>Remove</button>
            </div>
        @endforeach
    </div>

    @error('details')<span class="error-message">{{ $message }}</span>@enderror

    <div class="form-actions">
        <button type="button" class="btn btn-secondary" data-add-row>Add Row</button>
        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Save Changes' : 'Create Timesheet' }}</button>
    </div>
</form>

<template data-timesheet-row-template>
    <div class="timesheet-row" data-timesheet-row>
        <label>
            <span>Project Name</span>
            <textarea data-name="project_name" rows="2" required></textarea>
        </label>
        <label>
            <span>Functions</span>
            <textarea data-name="functions" rows="2" required></textarea>
        </label>
        <label>
            <span>Status</span>
            <select data-name="status" required>
                <option value="Pending">Pending</option>
                <option value="Done">Done</option>
            </select>
        </label>
        <label>
            <span>Remark</span>
            <textarea data-name="remark" rows="2"></textarea>
        </label>
        <button type="button" class="btn btn-secondary timesheet-remove-row" data-remove-row>Remove</button>
    </div>
</template>

@once
    @push('scripts')
        <script>
            function refreshTimesheetRows($form) {
                $form.find('[data-timesheet-row]').each(function(index) {
                    $(this).find('[data-name]').each(function() {
                        $(this).attr('name', 'details[' + index + '][' + $(this).data('name') + ']');
                    });
                });

                var canRemove = $form.find('[data-timesheet-row]').length > 1;
                $form.find('[data-remove-row]').prop('disabled', !canRemove);
            }

            $(function() {
                $('[data-timesheet-form]').each(function() {
                    refreshTimesheetRows($(this));
                });

                $(document).on('click', '[data-add-row]', function() {
                    var $form = $(this).closest('[data-timesheet-form]');
                    var template = $form.siblings('[data-timesheet-row-template]').html();
                    $form.find('[data-timesheet-rows]').append(template);
                    refreshTimesheetRows($form);
                });

                $(document).on('click', '[data-remove-row]', function() {
                    var $form = $(this).closest('[data-timesheet-form]');
                    if ($form.find('[data-timesheet-row]').length <= 1) {
                        return;
                    }
                    $(this).closest('[data-timesheet-row]').remove();
                    refreshTimesheetRows($form);
                });
            });
        </script>
    @endpush
@endonce
