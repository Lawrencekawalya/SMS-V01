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
        Schema::create('grade_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_mark_id')->constrained('student_marks')->cascadeOnDelete();
            $table->foreignId('changed_by_id')->constrained('users')->restrictOnDelete();
            $table->string('score_type', 20);
            $table->decimal('old_score', 5, 2)->nullable();
            $table->decimal('new_score', 5, 2);
            $table->text('reason');
            $table->timestamps();

            $table->index(['student_mark_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_audit_logs');
    }
};
