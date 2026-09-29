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
        Schema::create('course_registration_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_registration_id')->constrained('course_registrations')->cascadeOnDelete();
            $table->foreignId('course_unit_id')->constrained('course_units')->restrictOnDelete();
            $table->string('course_type', 30)->default('Core');
            $table->decimal('credit_units', 3, 1);
            $table->string('status', 30)->default('registered');
            $table->timestamp('dropped_at')->nullable();
            $table->string('drop_reason')->nullable();
            $table->timestamps();

            // A course unit can only appear once in a registration slip
            $table->unique(['course_registration_id', 'course_unit_id']);
            $table->index(['course_registration_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_registration_items');
    }
};
