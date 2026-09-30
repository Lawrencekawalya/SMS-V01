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
        Schema::create('student_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_assessment_sheet_id')->constrained('course_assessment_sheets')->cascadeOnDelete();
            $table->foreignId('course_registration_item_id')->constrained('course_registration_items')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('ca_score', 5, 2)->nullable();
            $table->decimal('exam_score', 5, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->string('grade_letter', 5)->nullable();
            $table->decimal('grade_point', 3, 2)->nullable();
            $table->boolean('is_passed')->default(false);
            $table->boolean('is_retake')->default(false);
            $table->string('lecturer_remarks')->nullable();
            $table->timestamps();

            // One mark record per student per assessment sheet
            $table->unique(['course_assessment_sheet_id', 'student_id']);
            $table->index(['student_id', 'is_passed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_marks');
    }
};
