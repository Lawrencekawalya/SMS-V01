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
        Schema::create('academic_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->string('title', 150);
            $table->enum('event_type', ['academic_deadline', 'examination', 'lecture_period', 'holiday', 'governance', 'ceremony'])->default('academic_deadline');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_all_day')->default(true);
            $table->enum('target_audience', ['all', 'students', 'lecturers', 'freshers', 'staff'])->default('all');
            $table->boolean('is_holiday')->default(false);
            $table->text('description')->nullable();
            $table->string('color', 20)->nullable();
            $table->timestamps();

            $table->index(['academic_year_id', 'start_date']);
            $table->index(['semester_id', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_events');
    }
};
