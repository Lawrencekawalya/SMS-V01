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
        Schema::create('course_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('code')->unique()->index();
            $table->string('name');
            $table->decimal('credit_units', 3, 1)->default(3.0);
            $table->text('description')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_units');
    }
};
