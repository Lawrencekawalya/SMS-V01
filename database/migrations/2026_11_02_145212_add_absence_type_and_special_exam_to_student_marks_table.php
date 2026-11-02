<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            $table->string('exam_status', 30)->default('present')->after('exam_score'); // 'present', 'missed_permission', 'absent'
            $table->string('sitting_type', 30)->default('main')->after('exam_status');   // 'main', 'special', 'supplementary'
            $table->decimal('special_exam_score', 5, 2)->nullable()->after('sitting_type');
            $table->date('special_exam_date')->nullable()->after('special_exam_score');
            $table->text('special_exam_remarks')->nullable()->after('special_exam_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            $table->dropColumn([
                'exam_status',
                'sitting_type',
                'special_exam_score',
                'special_exam_date',
                'special_exam_remarks',
            ]);
        });
    }
};
