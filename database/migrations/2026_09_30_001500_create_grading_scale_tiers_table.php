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
        Schema::create('grading_scale_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('grade_letter', 5);
            $table->decimal('min_score', 5, 2);
            $table->decimal('max_score', 5, 2);
            $table->decimal('grade_point', 3, 2);
            $table->string('classification', 100);
            $table->string('badge_class', 50)->default('text-bg-secondary');
            $table->boolean('is_pass')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['min_score', 'max_score']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grading_scale_tiers');
    }
};
