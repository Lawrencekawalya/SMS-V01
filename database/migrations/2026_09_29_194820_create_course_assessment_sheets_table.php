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
        Schema::create('course_assessment_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_unit_id')->constrained('course_units')->restrictOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('ca_weight', 4, 1)->default(40.0);
            $table->decimal('exam_weight', 4, 1)->default(60.0);
            $table->decimal('pass_mark', 4, 1)->default(50.0);
            $table->string('status', 35)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('moderated_at')->nullable();
            $table->foreignId('moderated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('moderation_remarks')->nullable();
            $table->timestamps();

            // Strictly one assessment sheet per course unit per semester
            $table->unique(['course_unit_id', 'semester_id']);
            $table->index(['status', 'semester_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_assessment_sheets');
    }
};
