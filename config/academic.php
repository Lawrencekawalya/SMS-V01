<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Course Prerequisite Enforcement
    |--------------------------------------------------------------------------
    |
    | When enabled (true), students cannot register for or add a course unit
    | unless they have completed and passed all its prerequisite course units.
    | When disabled (false, default for Ugandan universities), prerequisites are
    | preserved in the background but do not act as hard blockers during course
    | enrollment and add/drop workflows.
    |
    */
    'enforce_prerequisites' => env('ENFORCE_COURSE_PREREQUISITES', false),

    /*
    |--------------------------------------------------------------------------
    | Course Registration Advisor Approval Policy
    |--------------------------------------------------------------------------
    |
    | When enabled (true), submitted student course registrations and add/drop
    | adjustments enter a pending queue awaiting manual approval by an Academic
    | Advisor or Head of Department.
    | When disabled (false, default for Ugandan universities), submitted
    | registrations are automatically confirmed/approved immediately upon submission,
    | and the manual advisor approval workflow is hidden from navigation.
    |
    */
    'require_registration_approval' => env('REQUIRE_REGISTRATION_APPROVAL', false),

    /*
    |--------------------------------------------------------------------------
    | Examination & Continuous Assessment (CA) Weightings
    |--------------------------------------------------------------------------
    |
    | Institutional default weightings for student course unit evaluation.
    | Typically 40% Continuous Assessment (Coursework, tests, practicals) and
    | 60% Final Examination (or 30% / 70% in select faculties).
    | Total sum of CA Weight and Exam Weight equals 100%.
    |
    */
    'assessment_ca_weight' => (float) env('ASSESSMENT_CA_WEIGHT', 40.0),
    'assessment_exam_weight' => (float) env('ASSESSMENT_EXAM_WEIGHT', 60.0),

    /*
    |--------------------------------------------------------------------------
    | Minimum Pass Mark
    |--------------------------------------------------------------------------
    |
    | The minimum final percentage score required to pass a course unit.
    | In Uganda and NCHE standards, the standard undergraduate pass mark is 50.0%.
    | Scores below this threshold result in an 'F' grade and require a retake.
    |
    */
    'assessment_pass_mark' => (float) env('ASSESSMENT_PASS_MARK', 50.0),

    /*
    |--------------------------------------------------------------------------
    | National Council for Higher Education (NCHE) 5.0 Grading Scale
    |--------------------------------------------------------------------------
    |
    | Official Ugandan collegiate letter grades, grade points (GP), and
    | academic achievement classifications.
    |
    */
    'grading_scale' => [
        [
            'min_score' => 80.0,
            'max_score' => 100.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'classification' => 'Exceptional / Distinction',
            'badge_class' => 'text-bg-success',
        ],
        [
            'min_score' => 75.0,
            'max_score' => 79.99,
            'grade_letter' => 'B+',
            'grade_point' => 4.5,
            'classification' => 'Very Good',
            'badge_class' => 'text-bg-success',
        ],
        [
            'min_score' => 70.0,
            'max_score' => 74.99,
            'grade_letter' => 'B',
            'grade_point' => 4.0,
            'classification' => 'Good',
            'badge_class' => 'text-bg-primary',
        ],
        [
            'min_score' => 65.0,
            'max_score' => 69.99,
            'grade_letter' => 'C+',
            'grade_point' => 3.5,
            'classification' => 'Fairly Good',
            'badge_class' => 'text-bg-info',
        ],
        [
            'min_score' => 60.0,
            'max_score' => 64.99,
            'grade_letter' => 'C',
            'grade_point' => 3.0,
            'classification' => 'Clear Pass',
            'badge_class' => 'text-bg-info',
        ],
        [
            'min_score' => 55.0,
            'max_score' => 59.99,
            'grade_letter' => 'D+',
            'grade_point' => 2.5,
            'classification' => 'Marginal Pass',
            'badge_class' => 'text-bg-warning',
        ],
        [
            'min_score' => 50.0,
            'max_score' => 54.99,
            'grade_letter' => 'D',
            'grade_point' => 2.0,
            'classification' => 'Pass (Minimum Passing Grade)',
            'badge_class' => 'text-bg-warning',
        ],
        [
            'min_score' => 0.0,
            'max_score' => 49.99,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'classification' => 'Fail (Requires Retake)',
            'badge_class' => 'text-bg-danger',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Uganda NCHE Degree Classifications (CGPA Ranges)
    |--------------------------------------------------------------------------
    |
    | Official undergraduate degree awards and honours classifications
    | based on cumulative grade point average (CGPA) on a 5.0 scale.
    |
    */
    'degree_classifications' => [
        [
            'min_cgpa' => 4.40,
            'max_cgpa' => 5.00,
            'name' => 'First Class Honours',
            'badge_class' => 'text-bg-success',
            'academic_standing' => 'Normal Progress',
        ],
        [
            'min_cgpa' => 3.60,
            'max_cgpa' => 4.39,
            'name' => 'Second Class Honours (Upper Division)',
            'badge_class' => 'text-bg-primary',
            'academic_standing' => 'Normal Progress',
        ],
        [
            'min_cgpa' => 2.80,
            'max_cgpa' => 3.59,
            'name' => 'Second Class Honours (Lower Division)',
            'badge_class' => 'text-bg-info',
            'academic_standing' => 'Normal Progress',
        ],
        [
            'min_cgpa' => 2.00,
            'max_cgpa' => 2.79,
            'name' => 'Pass Degree',
            'badge_class' => 'text-bg-warning',
            'academic_standing' => 'Normal Progress',
        ],
        [
            'min_cgpa' => 0.00,
            'max_cgpa' => 1.99,
            'name' => 'Fail / Academic Probation',
            'badge_class' => 'text-bg-danger',
            'academic_standing' => 'Probation',
        ],
    ],

];
