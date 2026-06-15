<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReportDetail extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'report_code',
        'project_name',
        'functions',
        'status',
        'remark',
    ];

    public function report()
    {
        return $this->belongsTo(DailyReport::class, 'report_code', 'report_code');
    }
}
