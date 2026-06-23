<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->unique(['employee_code', 'report_date'], 'daily_reports_employee_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropUnique('daily_reports_employee_date_unique');
        });
    }
};
