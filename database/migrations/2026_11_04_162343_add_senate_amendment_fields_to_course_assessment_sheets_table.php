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
        Schema::table('course_assessment_sheets', function (Blueprint $table) {
            $table->timestamp('reopened_at')->nullable()->after('published_by_id');
            $table->foreignId('reopened_by_id')->nullable()->after('reopened_at')->constrained('users')->nullOnDelete();
            $table->string('senate_approval_reference', 100)->nullable()->after('reopened_by_id');
            $table->text('reopen_justification')->nullable()->after('senate_approval_reference');
            $table->unsignedInteger('revision_cycle')->default(0)->after('reopen_justification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_assessment_sheets', function (Blueprint $table) {
            $table->dropForeign(['reopened_by_id']);
            $table->dropColumn([
                'reopened_at',
                'reopened_by_id',
                'senate_approval_reference',
                'reopen_justification',
                'revision_cycle',
            ]);
        });
    }
};
