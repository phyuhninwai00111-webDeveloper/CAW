@php
    $isEdit = filled($timesheetId ?? null);
    $formAction = $isEdit ? route('timesheets.update', $timesheetId) : route('timesheets.store');
    $formDetails = old('details');

    if (! $formDetails) {
        $formDetails = isset($report)
            ? (isset($selectedDetail) ? collect([$selectedDetail]) : $report->details)->map(fn ($detail) => [
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
    @if(isset($selectedDetail))
        <input type="hidden" name="detail_id" value="{{ old('detail_id', $selectedDetail->id) }}">
    @endif

    <div class="timesheet-meta-grid">
        {{-- @if(!$isEdit) --}}
             <label>
            <span>Date</span>
            <input type="date" name="report_date" value="{{ old('report_date', isset($report) ? $report->report_date->format('Y-m-d') : now()->toDateString()) }}" required>
            @error('report_date')<span class="error-message">{{ $message }}</span>@enderror
            </label>

            <label>
            {{-- <span>Report Code</span> --}}


            <input type="hidden" name="report_code" value="{{ old('report_code', $report->report_code ?? $displayCode) }}" readonly required>
            @error('report_code')<span class="error-message">{{ $message }}</span>@enderror
            </label>

    </div>

    <div class="timesheet-rows" data-timesheet-rows>
        <div class="timesheet-row" data-timesheet-row>
                <label><span>Project Name</span></label>
                <label><span>Functions</span> </label>
                <label><span>Status</span></label>
                <label><span>Remark</span></label>
                    <div></div>
                @foreach($formDetails as $index => $detail)


                    <textarea  name="details[{{ $index }}][project_name]" data-name="project_name" rows="1" required>{{ $detail['project_name'] ?? '' }}</textarea>
                    @error("details.$index.project_name")<span class="error-message">{{ $message }}</span>@enderror


                    <textarea name="details[{{ $index }}][functions]" data-name="functions" rows="1" required>{{ $detail['functions'] ?? '' }}</textarea>
                    @error("details.$index.functions")<span class="error-message">{{ $message }}</span>@enderror


                    <select name="details[{{ $index }}][status]" data-name="status" required>
                        <option value="Pending" @selected(($detail['status'] ?? 'Pending') === 'Pending')>Pending</option>
                        <option value="Done" @selected(($detail['status'] ?? '') === 'Done')>Done</option>
                    </select>
                    @error("details.$index.status")<span class="error-message">{{ $message }}</span>@enderror


                    <textarea name="details[{{ $index }}][remark]" data-name="remark" rows="1">{{ $detail['remark'] ?? '' }}</textarea>
                    @error("details.$index.remark")<span class="error-message">{{ $message }}</span>@enderror

                <button type="button" class="btn btn-secondary btn-sm timesheet-remove-row"  data-remove-row>Remove</button>
        </div>

        @endforeach
</div>
    @error('details')<span class="error-message">{{ $message }}</span>@enderror

    <div class="form-actions">
         @if(!$isEdit) <button type="button" class="btn btn-secondary" data-add-row>Add Row</button> @endif
         <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Save Changes' : 'Create Timesheet' }}</button>
    </div>
</form>

 <template class="timesheet-rows-template " data-timesheet-row-template data-timesheet-rows>
    <div class="timesheet-row" data-timesheet-row>
         {{-- <label>
                    <span>Functions</span></label>
            <span>Project Name</span> <label>
                </label>
        <label>
            <span>Status</span> </label>
            <label>
            <span>Remark</span></label> --}}

            <textarea data-name="project_name" rows="1" required></textarea>

            <textarea data-name="functions" rows="1" required></textarea>


            <select data-name="status" required>
                <option value="Pending">Pending</option>
                <option value="Done">Done</option>
            </select>


            <textarea data-name="remark" rows="1"></textarea>

        <button type="button" class="btn btn-secondary timesheet-remove-row" data-remove-row>Remove</button>
    </div>
</template>

@once
    @push('scripts')
        <script>
           var $form = $(this).closest('[data-timesheet-form]');
           var remainingRows = $form.find('[data-timesheet-row]').length;
            //const isEditMode = window.location.search.includes('edit=1');

            function refreshTimesheetRows($form) {
                $form.find('[data-timesheet-row]').each(function(index) {
                    $(this).find('[data-name]').each(function() {
                        $(this).attr('name', 'details[' + index + '][' + $(this).data('name') + ']');
                    });
                });

            const isEditMode = window.location.search.includes('edit=1');
            var remainingRows = $form.find('[data-timesheet-row]').length;
                var canRemove = isEditMode || remainingRows > 1;
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

                //const currentMode = "{{ request()->query('mode') }}";
                $(document).on('click', '[data-remove-row]', function() {
                    var $row = $(this).closest('[data-timesheet-row]');
                    var $form = $(this).closest('[data-timesheet-form]');
                    var isEditMode = window.location.search.includes('edit=1');
                    var remainingRows = $form.find('[data-timesheet-row]').length;
                    if ( !isEditMode && remainingRows <= 1) {
                        alert('At least one row is required.');
                        return;
                    }
                    if (remainingRows < 1) {
                        var $form = $(this).closest('[data-timesheet-form]');
                         $form.hide();}
                         else {
                    $row.remove();
                    // $(this).closest('[data-timesheet-row]').remove();
                    refreshTimesheetRows($form);
                    }
        });
            });

        // Date ပြောင်းတိုင်း Code အသစ် Generate လုပ်မယ်
        // $('input[name="report_date"]').on('change', function() {
        // let date = $(this).val(); // ဥပမာ 2026-06-18
        // // နေ့စွဲပေါ်မူတည်ပြီး code အသစ်ဆောက် (TL- + ရက်စွဲ)
        // let formattedDate = date.replace(/-/g, '');
        // let newCode = "TL-" + formattedDate;
        // $('input[name="report_code"]').val(newCode);
        // });

        </script>
    @endpush
@endonce
