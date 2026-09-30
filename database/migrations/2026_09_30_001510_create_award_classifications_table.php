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
        Schema::create('award_classifications', function (Blueprint $table) {
            $table->id();
            $table->string('award_level', 30); // degree, diploma, certificate
            $table->string('name', 100);
            $table->decimal('min_cgpa', 3, 2);
            $table->decimal('max_cgpa', 3, 2);
            $table->string('badge_class', 50)->default('text-bg-secondary');
            $table->string('academic_standing', 40)->default('Normal Progress');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['award_level', 'min_cgpa', 'max_cgpa']);
            $table->index(['award_level', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('award_classifications');
    }
};
