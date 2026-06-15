<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';

    public $timestamps = false;

    protected $fillable = ['employee_code', 'attendance_date', 'check_in', 'check_out', 'report_code', 'created_at'];

    protected $dates = ['attendance_date', 'check_in', 'check_out'];

    public function user()
    {
        return $this->belongsTo(User::class, 'employee_code', 'employee_code');
    }

    public function report()
    {
        return $this->belongsTo(DailyReport::class, 'report_code', 'report_code');
    }
}
