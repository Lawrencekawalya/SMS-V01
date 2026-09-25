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
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->string('award_type', 30);
            $table->unsignedTinyInteger('duration_years')->default(3);
            $table->unsignedSmallInteger('required_credits_to_graduate')->default(110);
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};
