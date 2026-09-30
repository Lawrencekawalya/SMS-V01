<?php

namespace Database\Factories;

use App\Models\GradeAuditLog;
use App\Models\StudentMark;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeAuditLog>
 */
class GradeAuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_mark_id' => StudentMark::factory(),
            'changed_by_id' => User::factory(),
            'score_type' => 'exam',
            'old_score' => 45.0,
            'new_score' => 52.0,
            'reason' => 'Recount of section B questions upon student remark request.',
        ];
    }
}
