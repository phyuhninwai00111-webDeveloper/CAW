<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'employee_code',
        'report_code',
        'report_date',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_code', 'employee_code');
    }

    public function details()
    {
        return $this->hasMany(DailyReportDetail::class, 'report_code', 'report_code');
    }
}
