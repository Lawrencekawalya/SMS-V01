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
        Schema::create('student_semester_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->decimal('credit_units_registered', 4, 1)->default(0.0);
            $table->decimal('credit_units_earned', 4, 1)->default(0.0);
            $table->decimal('weighted_grade_points', 6, 2)->default(0.0);
            $table->decimal('gpa', 3, 2)->default(0.0);
            $table->decimal('cumulative_credit_units_registered', 5, 1)->default(0.0);
            $table->decimal('cumulative_credit_units_earned', 5, 1)->default(0.0);
            $table->decimal('cumulative_weighted_grade_points', 7, 2)->default(0.0);
            $table->decimal('cgpa', 3, 2)->default(0.0);
            $table->string('academic_standing', 40)->default('Normal Progress');
            $table->timestamps();

            // Strictly one performance record per student per semester
            $table->unique(['student_id', 'semester_id']);
            $table->index(['student_id', 'academic_standing']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_semester_performances');
    }
};
