<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // ဖိုင်ရဲ့ အပေါ်ဆုံးမှာ ထည့်ပါ


class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $roleId = (int) $user->role_id;

        $query = Attendance::query()
            ->select([
                'attendance.*',
                'users.name',
                'users.employee_code',
                'departments.department_name',
                'daily_reports.report_code' // Add this line
            ])
            ->join('users', 'users.employee_code', '=', 'attendance.employee_code')
            ->leftJoin('departments', 'departments.id', '=', 'users.department_id')
            ->leftJoin('daily_reports', function($join) {
                $join->on('daily_reports.employee_code', '=', 'attendance.employee_code')
                ->on('daily_reports.report_date', '=', 'attendance.attendance_date');
    }); // This joins based on matching employee and date

        if ($roleId === 2) {
            $query->where('users.department_id', $user->department_id);
        } elseif ($roleId === 3) {
            $query->where('attendance.employee_code', $user->employee_code);
        }

        if ($request->filled('from')) {
            $query->where('attendance.attendance_date', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->where('attendance.attendance_date', '<=', $request->query('to'));
        }
        if ($roleId === 1 && $request->filled('department_id')) {
            $query->where('users.department_id', $request->query('department_id'));
        }
        if (($roleId === 1 || $roleId === 2) && $request->filled('employee_code')) {
            $query->where('attendance.employee_code', 'like', '%' . $request->query('employee_code') . '%');
        }

        $rows = $query->orderBy('attendance_date', 'desc')->orderBy('check_in', 'desc')->get()->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'attendance_date' => $attendance->attendance_date ? Carbon::parse($attendance->attendance_date)->format('Y-m-d') : null,
                'check_in' => $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i:s') : null,
                'check_out' => $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i:s') : null,
                'report_code' => $attendance->report_code,
                'name' => $attendance->name,
                'department_name' => $attendance->department_name,
            ];
        });

        return response()->json(['role_id' => $roleId, 'data' => $rows]);
    }

    public function checkIn(Request $request)
    {
        $user = Auth::user();
        $employeeCode = $user->employee_code;

        if (!$employeeCode) {
            return response()->json(['error' => 'Employee code missing from your account.'], 400);
        }

        $today = now()->toDateString();
        $attendance = Attendance::firstOrNew(['employee_code' => $employeeCode, 'attendance_date' => $today]);

        if ($attendance->exists && $attendance->check_in) {
            return response()->json(['error' => 'You have already checked in for today.'], 400);
        }

        $attendance->check_in = now();
        $attendance->save();

        return response()->json(['success' => true, 'check_in' => now()->format('H:i:s')]);
    }

    public function checkOut(Request $request)
    {
        $user = Auth::user();
        $employeeCode = $user->employee_code;

        if (!$employeeCode) {
            return response()->json(['error' => 'Employee code missing from your account.'], 400);
        }

        $today = now()->toDateString();
        $attendance = Attendance::where('employee_code', $employeeCode)
            ->where('attendance_date', $today)
            ->first();

        if (!$attendance) {
            return response()->json(['error' => 'No check-in found for today.'], 400);
        }
        if (!$attendance->check_in) {
            return response()->json(['error' => 'You have not checked in yet.'], 400);
        }
        if ($attendance->check_out) {
            return response()->json(['error' => 'You already checked out for today.'], 400);
        }

        $attendance->check_out = now();
        $attendance->save();

        return response()->json(['success' => true, 'check_out' => now()->format('H:i:s')]);
    }

    public function summary(Request $request)
    {
        $user = Auth::user();
        $roleId = $user->role_id;

        $query = Attendance::query()->join('users', 'users.employee_code', '=', 'attendance.employee_code');

        if ($roleId === 2) {
            $query->where('users.department_id', $user->department_id);
        } elseif ($roleId === 3) {
            $query->where('attendance.employee_code', $user->employee_code);
        }

        if ($request->filled('from')) {
            $query->where('attendance.attendance_date', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->where('attendance.attendance_date', '<=', $request->query('to'));
        }

        $total = $query->count();
        $late = (clone $query)->whereTime('check_in', '>', '09:00:00')->count();

        return response()->json(['total' => $total, 'late' => $late]);
    }
        public function checkCode(Request $request)
    {
        $date = $request->query('date');
        $user = auth()->user();

        // Find a record for this user on this date
        $existing = Timesheet::where('date', $date)
                            ->where('user_id', $user->id)
                            ->first();

        return response()->json([
            'code' => $existing ? $existing->report_code : null
        ]);
    }
}
