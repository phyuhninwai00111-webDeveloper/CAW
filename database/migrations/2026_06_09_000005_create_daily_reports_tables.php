<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code');
            $table->string('report_code')->unique();
            $table->date('report_date');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('employee_code')->references('employee_code')->on('users')->onUpdate('cascade');
        });

        Schema::create('daily_report_details', function (Blueprint $table) {
            $table->id();
            $table->string('report_code');
            $table->text('project_name');
            $table->text('functions');
            $table->string('status');
            $table->string('remark')->nullable();
            $table->foreign('report_code')->references('report_code')->on('daily_reports')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_report_details');
        Schema::dropIfExists('daily_reports');
    }
};
