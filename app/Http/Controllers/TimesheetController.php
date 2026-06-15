<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\DailyReport;
use App\Models\DailyReportDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TimesheetController extends Controller
{
    protected function canViewReport(DailyReport $report)
    {
        $user = Auth::user();
        $roleId = (int) $user->role_id;

        if ($roleId === 1) {
            return true;
        }

        if ($roleId === 2) {
            return $report->employee->department_id === $user->department_id;
        }

        return $report->employee_code === $user->employee_code;
    }

    protected function canEditReport(DailyReport $report)
    {
        $user = Auth::user();
        $roleId = (int) $user->role_id;

        if ($roleId === 1) {
            return true;
        }

        if ($roleId === 2) {
            return $report->employee->department_id === $user->department_id;
        }

        return $report->employee_code === $user->employee_code;
    }

    protected function reportQuery()
    {
        $user = Auth::user();
        $roleId = (int) $user->role_id;

        $query = DailyReport::query()
            ->select([ 'daily_reports.*', 'users.name', 'users.department_id', 'departments.department_name' ])
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
    $query = $this->reportQuery();
    $reports = $query->with('details')->orderBy('report_date', 'desc')->get();

    $user = Auth::user();

    $today = now()->toDateString();
    $existingReport = DailyReport::where('employee_code', $user->employee_code)
                                 ->where('report_date', $today)
                                 ->first();

    // ဒီနေရာမှာ $isHr ကို သတ်မှတ်ပေးဖို့ လိုပါတယ်
    $isHr = Auth::user()->role_id == 1;
    $displayCode = $existingReport ? $existingReport->report_code : 'TL-' . strtoupper(Str::random(6));
    $isExisting = $existingReport ? true : false;
    return view('timesheets.index', [
        'reports' => $reports,
       // 'reports' => $this->reportQuery()->get(),
        'displayCode' => $displayCode, // Define these
        'isExisting' => $isExisting,
        'isHr' => $isHr, // ဒီလိုင်းလေး ထည့်ပေးလိုက်ပါ
        'canSearchEmployeeCode' => Auth::user()->role_id == 1 || Auth::user()->role_id == 2,
        // Pass the code if it exists, otherwise null
        'existingCode' => $existingReport ? $existingReport->report_code : null
    ]);
}

    public function show($reportCode)
     {
        //    original
        // $report = DailyReport::where('report_code', $reportCode)->firstOrFail();
        // $report->load('employee', 'details');
        $report = DailyReport::with('employee', 'details')
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
    $request->validate([
        'report_date' => 'required|date',
        'report_code' => 'required|string',
        'project_name' => 'required|string',
        'functions' => 'required|string',
        'status' => 'required|string|in:Done,Pending',
        'remark' => 'nullable|string',
    ]);

    $user = Auth::user();


    //to add firstOrCreate method()
        $report = DailyReport::firstOrCreate(
        [
            'employee_code' => Auth::user()->employee_code,
            'report_date' => $request->report_date
        ],
        [
            'report_code' => $request->report_code
        ]
    );

    // to save the detail record
    DailyReportDetail::create([
        'report_code' => $report->report_code, // Use the shared code
        'project_name' => $request->project_name,
        'functions' => $request->functions,
        'status' => $request->status,
        'remark' => $request->remark,
    ]);

    return redirect()->route('timesheets.index')->with('success', 'Timesheet added successfully.');
}

    public function update(Request $request, $id)
    {


        $request->validate([
            'project_name' => 'required|string',
            'functions' => 'required|string',
            'status' => 'required|string|in:Done,Pending',
            'remark' => 'required|string',
        ]);


        $detail = DailyReportDetail::findOrFail($id);

        $detail->update([
            'project_name' => $request->input('project_name'),
            'functions' => $request->input('functions'),
            'status' => $request->input('status'),
            'remark' => $request->input('remark'),
        ]);
        $detail->save();
//                                         ,$report->report_code
        return redirect()->route('timesheets.show' )->with('success', 'Timesheet updated successfully.');
    }
}

 // $reports = DailyReport::where('employee_code', $user->employee_code)
    //                       ->orderBy('report_date', 'desc')
    //                       ->get();
//     public function store(Request $request)
//     {
//         $request->validate([
//             'report_code' => 'required|string|max:255|unique:daily_reports,report_code',
//             'report_date' => 'required|date',
//             'project_name' => 'required|string',
//             'functions' => 'required|string',
//             'status' => 'required|string|in:Done,Pending',
//             'remark' => 'required|string',
//         ]);

//         $user = Auth::user();
//         $report = DailyReport::where('employee_code', $user->employee_code)
//                           ->where('report_date', $request->report_date)
//                           ->first();
//         if (!$report) {
//         $report = DailyReport::create([
//             'employee_code' => $user->employee_code,
//             'report_date' => $request->report_date,
//             'report_code' => $request->report_code,
//         ]);
//     }

//        DailyReportDetail::create([
//         'report_code' => $report->report_code,
//         'project_name' => $request->project_name,
//         'functions' => $request->functions,
//         'status' => $request->status,
//         'remark' => $request->remark,
//     ]);
//        Attendance::where('employee_code', $user->employee_code)
//               ->whereDate('attendance_date', $report->report_date)
//               ->update(['report_code' => $report->report_code]);

//     return redirect()->route('timesheets.show', $report->report_code)->with('success', 'Timesheet added successfully.');
// }
  // 1. ALWAYS try to find an existing report for this user and date
    // $report = DailyReport::where('employee_code', $user->employee_code)
    //                      ->where('report_date', $request->report_date)
    //                      ->first();


 // 2. If it doesn't exist, create the header record (DailyReport)
    // if (!$report) {
    //     $reportCode = 'TS-' . strtoupper(Str::random(6));
    //     $report = DailyReport::create([
    //         'employee_code' => $user->employee_code,
    //         'report_date' => $request->report_date,
    //         'report_code' => $request->$reportCode,
    //     ]);
    // }

    // 3. Now use the $report->report_code (whether it was found or newly created)
// public function updateDetail(Request $request, $id) // detail ID ကို parameter အဖြစ် လက်ခံမယ်
// {
//     $request->validate([
//         'project_name' => 'required|string',
//         'functions'    => 'required|string',
//         'status'       => 'required|string|in:Done,Pending',
//         'remark'       => 'required|string',
//     ]);

//     // ရွေးချယ်လိုက်တဲ့ detail id နဲ့ သက်ဆိုင်တဲ့ record ကိုပဲ ရှာမယ်
//     $detail = DailyReportDetail::findOrFail($id);

//     $detail->update([
//         'project_name' => $request->input('project_name'),
//         'functions'    => $request->input('functions'),
//         'status'       => $request->input('status'),
//         'remark'       => $request->input('remark'),
//     ]);

//     return redirect()->back()->with('success', 'Timesheet updated successfully.');
// }

// $report = DailyReport::where('report_code', $reportCode)->firstOrFail();
        //  $report->load('details');

        // if (! $this->canEditReport($report)) {
        //     abort(403);
        // }
        // $detail = $report->details->first();
        // if (! $detail) {
        //     $detail = new DailyReportDetail(['report_code' => $report->report_code]);
        // }
   // $detail->update($request->all());
//fill->update
