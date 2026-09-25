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
        Schema::create('curriculums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained('programmes')->cascadeOnDelete();
            $table->string('version_name', 100);
            $table->unsignedSmallInteger('start_academic_year');
            $table->unsignedSmallInteger('end_academic_year')->nullable();
            $table->unsignedSmallInteger('min_graduation_credits');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['programme_id', 'version_name']);
        });

        Schema::create('curriculum_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_id')->constrained('curriculums')->cascadeOnDelete();
            $table->foreignId('course_unit_id')->constrained('course_units')->cascadeOnDelete();
            $table->unsignedTinyInteger('study_year');
            $table->unsignedTinyInteger('semester');
            $table->enum('course_type', ['Core', 'Elective', 'Audited'])->default('Core');
            $table->timestamps();

            $table->unique(['curriculum_id', 'course_unit_id']);
            $table->index(['curriculum_id', 'study_year', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_courses');
        Schema::dropIfExists('curriculums');
    }
};
