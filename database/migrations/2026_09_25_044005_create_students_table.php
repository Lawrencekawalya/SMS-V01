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
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // Institutional & Identity Identifiers
            $table->string('registration_number')->unique();
            $table->string('student_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('other_names')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->default('male');
            $table->date('date_of_birth')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('national_id_nin')->nullable();

            // Academic Placement (Week 1 Foundation)
            $table->foreignId('campus_id')->constrained('campuses')->restrictOnDelete();
            $table->foreignId('programme_id')->constrained('programmes')->restrictOnDelete();
            $table->foreignId('curriculum_id')->constrained('curriculums')->restrictOnDelete();
            $table->foreignId('admission_academic_year_id')->constrained('academic_years')->restrictOnDelete();

            // Academic Structure & Modes
            $table->string('study_mode')->default('Day'); // Day, Evening, Weekend
            $table->string('intake')->default('August'); // August (Main), January

            // Academic Standing & Stage (Drives Week 2 Course Registration)
            $table->unsignedTinyInteger('current_study_year')->default(1); // 1, 2, 3, 4
            $table->unsignedTinyInteger('current_semester')->default(1); // 1, 2
            $table->string('status')->default('active'); // active, probation, suspended, completed, discontinued
            $table->decimal('cumulative_gpa', 3, 2)->default(0.00);

            // Optional User Account and Advisor FK
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('academic_advisor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Indexes for fast lookups in registration
            $table->index(['programme_id', 'curriculum_id']);
            $table->index(['current_study_year', 'current_semester']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
