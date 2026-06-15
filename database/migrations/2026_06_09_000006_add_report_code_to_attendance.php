<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->string('report_code')->nullable()->after('check_out');
            $table->foreign('report_code')->nullable()->references('report_code')->on('daily_reports')->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropForeign(['report_code']);
            $table->dropColumn('report_code');
        });
    }
};
