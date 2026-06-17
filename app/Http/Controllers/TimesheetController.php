<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\DailyReport;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TimesheetController extends Controller
{
    protected function canViewReport(DailyReport $report): bool
    {
        $user = Auth::user();
        $roleId = (int) $user->role_id;

        if ($roleId === 1) {
            return true;
        }

        if ($roleId === 2) {
            return (int) $report->employee->department_id === (int) $user->department_id;
        }

        return $report->employee_code === $user->employee_code;
    }

    protected function canEditReport(DailyReport $report): bool
    {
        return $this->canViewReport($report);
    }

    protected function reportQuery()
    {
        $user = Auth::user();
        $roleId = (int) $user->role_id;

        $query = DailyReport::query()
            ->select(['daily_reports.*', 'users.name', 'users.department_id', 'departments.department_name'])
            ->join('users', 'users.employee_code', '=', 'daily_reports.employee_code')
            ->leftJoin('departments', 'departments.id', '=', 'users.department_id');

        if ($roleId === 2) {
            $query->where('users.department_id', $user->department_id);
        } elseif ($roleId === 3) {
            $query->where('daily_reports.employee_code', $user->employee_code);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $isHr = (int) $user->role_id === 1;
        $query = $this->reportQuery();

        if ($isHr && $request->filled('department_id')) {
            $query->where('users.department_id', $request->query('department_id'));
        }

        $reports = $query->with('details')->orderBy('report_date', 'desc')->get();
        $existingReport = DailyReport::where('employee_code', $user->employee_code)
            ->where('report_date', now()->toDateString())
            ->first();

        return view('timesheets.index', [
            'reports' => $reports,
            'departments' => Department::orderBy('department_name')->get(),
            'selectedDepartmentId' => $request->query('department_id'),
            'displayCode' => $existingReport ? $existingReport->report_code : 'TL-' . strtoupper(Str::random(6)),
            'isExisting' => (bool) $existingReport,
            'isHr' => $isHr,
            'canSearchEmployeeCode' => in_array((int) $user->role_id, [1, 2], true),
            'existingCode' => $existingReport?->report_code,
        ]);
    }

    public function show(string $reportCode)
    {
        $report = DailyReport::with('employee.department', 'details')
            ->where('report_code', $reportCode)
            ->firstOrFail();

        if (! $this->canViewReport($report)) {
            abort(403);
        }

        return view('timesheets.show', [
            'report' => $report,
            'canEdit' => $this->canEditReport($report),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedTimesheet($request);
        $user = Auth::user();

        $report = DailyReport::firstOrCreate(
            [
                'employee_code' => $user->employee_code,
                'report_date' => $data['report_date'],
            ],
            [
                'report_code' => $data['report_code'],
            ]
        );

        foreach ($data['details'] as $detail) {
            $report->details()->create($detail);
        }

        Attendance::where('employee_code', $user->employee_code)
            ->whereDate('attendance_date', $report->report_date)
            ->update(['report_code' => $report->report_code]);

        return redirect()->route('timesheets.show', $report->report_code)->with('success', 'Timesheet added successfully.');
    }

    public function update(Request $request, string $reportCode)
    {
        $report = DailyReport::with('employee', 'details')
            ->where('report_code', $reportCode)
            ->firstOrFail();

        if (! $this->canEditReport($report)) {
            abort(403);
        }

        $data = $this->validatedTimesheet($request, $report->report_code);
        $report->update(['report_date' => $data['report_date']]);
        $report->details()->delete();

        foreach ($data['details'] as $detail) {
            $report->details()->create($detail);
        }

        Attendance::where('employee_code', $report->employee_code)
            ->whereDate('attendance_date', $report->report_date)
            ->update(['report_code' => $report->report_code]);

        return redirect()->route('timesheets.show', $report->report_code)->with('success', 'Timesheet updated successfully.');
    }

    protected function validatedTimesheet(Request $request, ?string $reportCode = null): array
    {
        if (! $request->has('details') && $request->filled('project_name')) {
            $request->merge([
                'details' => [[
                    'project_name' => $request->input('project_name'),
                    'functions' => $request->input('functions'),
                    'status' => $request->input('status'),
                    'remark' => $request->input('remark'),
                ]],
            ]);
        }

        $data = $request->validate([
            'report_date' => ['required', 'date'],
            'report_code' => ['required', 'string'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.project_name' => ['required', 'string'],
            'details.*.functions' => ['required', 'string'],
            'details.*.status' => ['required', 'string', 'in:Done,Pending'],
            'details.*.remark' => ['nullable', 'string'],
        ]);

        $data['report_code'] = $reportCode ?: $data['report_code'];
        $data['details'] = collect($data['details'])->map(fn ($detail) => [
            'project_name' => $detail['project_name'],
            'functions' => $detail['functions'],
            'status' => $detail['status'],
            'remark' => $detail['remark'] ?? null,
        ])->values()->all();

        return $data;
    }
}
