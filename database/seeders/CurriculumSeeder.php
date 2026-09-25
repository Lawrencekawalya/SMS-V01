<?php

namespace Database\Seeders;

use App\Models\CourseUnit;
use App\Models\Curriculum;
use App\Models\CurriculumCourse;
use App\Models\Programme;
use Illuminate\Database\Seeder;

class CurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bscs = Programme::where('code', 'BSCS')->first();
        $bsse = Programme::where('code', 'BSSE')->first();
        $bit = Programme::where('code', 'BIT')->first();

        // 1. BSCS 2024-2027 Curriculum
        if ($bscs) {
            $curriculumBscs = Curriculum::updateOrCreate(
                [
                    'programme_id' => $bscs->id,
                    'version_name' => '2024-2027 Standard CS Curriculum',
                ],
                [
                    'start_academic_year' => 2024,
                    'end_academic_year' => 2027,
                    'min_graduation_credits' => 110,
                    'is_active' => true,
                ]
            );

            $bscsMappings = [
                // Year 1, Sem 1
                ['code' => 'CSC1101', 'year' => 1, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'CSC1102', 'year' => 1, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'BIT1101', 'year' => 1, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'AFN1102', 'year' => 1, 'sem' => 1, 'type' => 'Elective'],
                // Year 1, Sem 2
                ['code' => 'CSC1201', 'year' => 1, 'sem' => 2, 'type' => 'Core'],
                ['code' => 'CSC1202', 'year' => 1, 'sem' => 2, 'type' => 'Core'],
                ['code' => 'BIT1201', 'year' => 1, 'sem' => 2, 'type' => 'Core'],
                // Year 2, Sem 1
                ['code' => 'CSC2101', 'year' => 2, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'CSC2102', 'year' => 2, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'BIT2101', 'year' => 2, 'sem' => 1, 'type' => 'Core'],
                // Year 2, Sem 2
                ['code' => 'BIT2201', 'year' => 2, 'sem' => 2, 'type' => 'Core'],
                ['code' => 'AFN2101', 'year' => 2, 'sem' => 2, 'type' => 'Elective'],
                // Year 3, Sem 1
                ['code' => 'CSC3101', 'year' => 3, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'BIT3101', 'year' => 3, 'sem' => 1, 'type' => 'Core'],
                // Year 3, Sem 2
                ['code' => 'CSC3201', 'year' => 3, 'sem' => 2, 'type' => 'Core'],
            ];

            foreach ($bscsMappings as $m) {
                $course = CourseUnit::where('code', $m['code'])->first();
                if ($course) {
                    CurriculumCourse::updateOrCreate(
                        [
                            'curriculum_id' => $curriculumBscs->id,
                            'course_unit_id' => $course->id,
                        ],
                        [
                            'study_year' => $m['year'],
                            'semester' => $m['sem'],
                            'course_type' => $m['type'],
                        ]
                    );
                }
            }
        }

        // 2. BSSE 2025-2029 Curriculum
        if ($bsse) {
            $curriculumBsse = Curriculum::updateOrCreate(
                [
                    'programme_id' => $bsse->id,
                    'version_name' => '2025-2029 Software Engineering Structure',
                ],
                [
                    'start_academic_year' => 2025,
                    'end_academic_year' => 2029,
                    'min_graduation_credits' => 120,
                    'is_active' => true,
                ]
            );

            $bsseMappings = [
                ['code' => 'CSC1101', 'year' => 1, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'CSC1102', 'year' => 1, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'MTH1102', 'year' => 1, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'CSC1201', 'year' => 1, 'sem' => 2, 'type' => 'Core'],
                ['code' => 'CSC1202', 'year' => 1, 'sem' => 2, 'type' => 'Core'],
                ['code' => 'CSC2101', 'year' => 2, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'BIT2101', 'year' => 2, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'CSC3101', 'year' => 3, 'sem' => 1, 'type' => 'Core'],
            ];

            foreach ($bsseMappings as $m) {
                $course = CourseUnit::where('code', $m['code'])->first();
                if ($course) {
                    CurriculumCourse::updateOrCreate(
                        [
                            'curriculum_id' => $curriculumBsse->id,
                            'course_unit_id' => $course->id,
                        ],
                        [
                            'study_year' => $m['year'],
                            'semester' => $m['sem'],
                            'course_type' => $m['type'],
                        ]
                    );
                }
            }
        }

        // 3. BIT 2024-2027 Curriculum
        if ($bit) {
            $curriculumBit = Curriculum::updateOrCreate(
                [
                    'programme_id' => $bit->id,
                    'version_name' => '2024-2027 IT Curriculum',
                ],
                [
                    'start_academic_year' => 2024,
                    'end_academic_year' => 2027,
                    'min_graduation_credits' => 100,
                    'is_active' => true,
                ]
            );

            $bitMappings = [
                ['code' => 'BIT1101', 'year' => 1, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'BIT1102', 'year' => 1, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'BIT1201', 'year' => 1, 'sem' => 2, 'type' => 'Core'],
                ['code' => 'BIT2101', 'year' => 2, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'BIT2201', 'year' => 2, 'sem' => 2, 'type' => 'Core'],
                ['code' => 'BIT3101', 'year' => 3, 'sem' => 1, 'type' => 'Core'],
            ];

            foreach ($bitMappings as $m) {
                $course = CourseUnit::where('code', $m['code'])->first();
                if ($course) {
                    CurriculumCourse::updateOrCreate(
                        [
                            'curriculum_id' => $curriculumBit->id,
                            'course_unit_id' => $course->id,
                        ],
                        [
                            'study_year' => $m['year'],
                            'semester' => $m['sem'],
                            'course_type' => $m['type'],
                        ]
                    );
                }
            }
        }

        // 4. DCA 2025-2027 Practical Curriculum (Diploma in Computer Applications)
        $dca = Programme::where('code', 'DCA')->first();
        if ($dca) {
            $curriculumDca = Curriculum::updateOrCreate(
                [
                    'programme_id' => $dca->id,
                    'version_name' => '2025-2027 Standard Diploma Curriculum',
                ],
                [
                    'start_academic_year' => 2025,
                    'end_academic_year' => 2027,
                    'min_graduation_credits' => 60,
                    'is_active' => true,
                ]
            );

            $dcaMappings = [
                // Year 1, Sem 1
                ['code' => 'CSC1101', 'year' => 1, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'BIT1101', 'year' => 1, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'BIT1102', 'year' => 1, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'AFN1102', 'year' => 1, 'sem' => 1, 'type' => 'Elective'],
                // Year 1, Sem 2
                ['code' => 'CSC1102', 'year' => 1, 'sem' => 2, 'type' => 'Core'],
                ['code' => 'BIT1201', 'year' => 1, 'sem' => 2, 'type' => 'Core'],
                // Year 2, Sem 1
                ['code' => 'CSC2101', 'year' => 2, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'BIT2101', 'year' => 2, 'sem' => 1, 'type' => 'Core'],
                // Year 2, Sem 2
                ['code' => 'BIT2201', 'year' => 2, 'sem' => 2, 'type' => 'Core'],
                ['code' => 'CSC1201', 'year' => 2, 'sem' => 2, 'type' => 'Core'],
                ['code' => 'AFN2101', 'year' => 2, 'sem' => 2, 'type' => 'Elective'],
            ];

            foreach ($dcaMappings as $m) {
                $course = CourseUnit::where('code', $m['code'])->first();
                if ($course) {
                    CurriculumCourse::updateOrCreate(
                        [
                            'curriculum_id' => $curriculumDca->id,
                            'course_unit_id' => $course->id,
                        ],
                        [
                            'study_year' => $m['year'],
                            'semester' => $m['sem'],
                            'course_type' => $m['type'],
                        ]
                    );
                }
            }
        }

        // 5. BBA 2024-2027 Curriculum (Bachelor of Business Administration)
        $bba = Programme::where('code', 'BBA')->first();
        if ($bba) {
            $curriculumBba = Curriculum::updateOrCreate(
                [
                    'programme_id' => $bba->id,
                    'version_name' => '2024-2027 Business Administration Curriculum',
                ],
                [
                    'start_academic_year' => 2024,
                    'end_academic_year' => 2027,
                    'min_graduation_credits' => 100,
                    'is_active' => true,
                ]
            );

            $bbaMappings = [
                ['code' => 'AFN1101', 'year' => 1, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'AFN1102', 'year' => 1, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'BIT1101', 'year' => 1, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'BIT1201', 'year' => 1, 'sem' => 2, 'type' => 'Core'],
                ['code' => 'MTH1102', 'year' => 1, 'sem' => 2, 'type' => 'Core'],
                ['code' => 'AFN2101', 'year' => 2, 'sem' => 1, 'type' => 'Core'],
                ['code' => 'CSC2101', 'year' => 2, 'sem' => 1, 'type' => 'Elective'],
                ['code' => 'BIT2201', 'year' => 2, 'sem' => 2, 'type' => 'Core'],
                ['code' => 'BIT3101', 'year' => 3, 'sem' => 1, 'type' => 'Core'],
            ];

            foreach ($bbaMappings as $m) {
                $course = CourseUnit::where('code', $m['code'])->first();
                if ($course) {
                    CurriculumCourse::updateOrCreate(
                        [
                            'curriculum_id' => $curriculumBba->id,
                            'course_unit_id' => $course->id,
                        ],
                        [
                            'study_year' => $m['year'],
                            'semester' => $m['sem'],
                            'course_type' => $m['type'],
                        ]
                    );
                }
            }
        }
    }
}
