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
        Schema::create('assessment_policies', function (Blueprint $table) {
            $table->id();
            $table->string('award_level', 30)->unique(); // degree, postgraduate, diploma, certificate
            $table->decimal('ca_weight', 5, 2)->default(40.0);
            $table->decimal('exam_weight', 5, 2)->default(60.0);
            $table->decimal('pass_mark', 5, 2)->default(50.0);
            $table->string('retake_cgpa_policy', 30)->default('replace_fail');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_policies');
    }
};
