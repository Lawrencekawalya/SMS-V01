<?php

namespace Database\Seeders;

use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class AcademicCalendarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Previous Academic Year (Closed / Past)
        $pastYear = AcademicYear::firstOrCreate(
            ['name' => '2025/2026'],
            [
                'start_date' => '2025-08-18',
                'end_date' => '2026-06-30',
                'is_current' => false,
                'description' => 'Academic Year 2025/2026 (Completed)',
            ]
        );

        Semester::firstOrCreate(
            ['academic_year_id' => $pastYear->id, 'semester_number' => 1],
            [
                'name' => 'Semester 1',
                'start_date' => '2025-08-18',
                'end_date' => '2025-12-19',
                'registration_start_date' => '2025-08-01',
                'registration_end_date' => '2025-09-05',
                'add_drop_deadline' => '2025-09-19',
                'is_active' => false,
            ]
        );

        Semester::firstOrCreate(
            ['academic_year_id' => $pastYear->id, 'semester_number' => 2],
            [
                'name' => 'Semester 2',
                'start_date' => '2026-01-12',
                'end_date' => '2026-05-22',
                'registration_start_date' => '2026-01-02',
                'registration_end_date' => '2026-01-30',
                'add_drop_deadline' => '2026-02-13',
                'is_active' => false,
            ]
        );

        // 2. Current Academic Year
        $currentYear = AcademicYear::firstOrCreate(
            ['name' => '2026/2027'],
            [
                'start_date' => '2026-08-17',
                'end_date' => '2027-06-25',
                'is_current' => true,
                'description' => 'Current University Academic Session 2026/2027',
            ]
        );

        $currentSem1 = Semester::firstOrCreate(
            ['academic_year_id' => $currentYear->id, 'semester_number' => 1],
            [
                'name' => 'Semester 1',
                'start_date' => '2026-08-17',
                'end_date' => '2026-12-18',
                'registration_start_date' => '2026-08-01',
                'registration_end_date' => '2026-09-18',
                'add_drop_deadline' => '2026-10-02',
                'is_active' => true,
            ]
        );

        $currentSem2 = Semester::firstOrCreate(
            ['academic_year_id' => $currentYear->id, 'semester_number' => 2],
            [
                'name' => 'Semester 2',
                'start_date' => '2027-01-11',
                'end_date' => '2027-05-21',
                'registration_start_date' => '2027-01-04',
                'registration_end_date' => '2027-01-29',
                'add_drop_deadline' => '2027-02-12',
                'is_active' => false,
            ]
        );

        // 3. Scheduled Academic Events & University Almanac
        $events = [
            [
                'academic_year_id' => $currentYear->id,
                'semester_id' => $currentSem1->id,
                'title' => "Freshers' Orientation & Matriculation Week",
                'event_type' => 'ceremony',
                'start_date' => '2026-08-10',
                'end_date' => '2026-08-15',
                'is_all_day' => true,
                'target_audience' => 'freshers',
                'is_holiday' => false,
                'description' => 'Campus tours, faculty inductions, library registration, and medical screening for all new entrants.',
            ],
            [
                'academic_year_id' => $currentYear->id,
                'semester_id' => $currentSem1->id,
                'title' => 'Commencement of Semester 1 Lectures',
                'event_type' => 'lecture_period',
                'start_date' => '2026-08-17',
                'end_date' => '2026-08-17',
                'is_all_day' => true,
                'target_audience' => 'all',
                'is_holiday' => false,
                'description' => 'First day of teaching across all faculties and departments according to published timetable.',
            ],
            [
                'academic_year_id' => $currentYear->id,
                'semester_id' => $currentSem1->id,
                'title' => 'Normal Course Registration Window Closes',
                'event_type' => 'academic_deadline',
                'start_date' => '2026-09-18',
                'end_date' => '2026-09-18',
                'is_all_day' => true,
                'target_audience' => 'students',
                'is_holiday' => false,
                'description' => 'Final day for students to complete initial course enrollment without late surcharge.',
            ],
            [
                'academic_year_id' => $currentYear->id,
                'semester_id' => $currentSem1->id,
                'title' => 'Course Add / Drop Final Cutoff',
                'event_type' => 'academic_deadline',
                'start_date' => '2026-10-02',
                'end_date' => '2026-10-02',
                'is_all_day' => true,
                'target_audience' => 'students',
                'is_holiday' => false,
                'description' => 'Strict system lock on student course lists; no course unit adjustments permitted after 5:00 PM.',
            ],
            [
                'academic_year_id' => $currentYear->id,
                'semester_id' => $currentSem1->id,
                'title' => 'National Independence Day Holiday',
                'event_type' => 'holiday',
                'start_date' => '2026-10-09',
                'end_date' => '2026-10-09',
                'is_all_day' => true,
                'target_audience' => 'all',
                'is_holiday' => true,
                'description' => 'Public holiday: all university offices and lecture rooms closed. Attendance not taken.',
            ],
            [
                'academic_year_id' => $currentYear->id,
                'semester_id' => $currentSem1->id,
                'title' => 'Continuous Assessment & Mid-Term Tests',
                'event_type' => 'examination',
                'start_date' => '2026-10-19',
                'end_date' => '2026-10-24',
                'is_all_day' => true,
                'target_audience' => 'students',
                'is_holiday' => false,
                'description' => 'Mid-term coursework tests (30% weight) administered across lecture venues.',
            ],
            [
                'academic_year_id' => $currentYear->id,
                'semester_id' => null, // Annual institutional event
                'title' => '21st Annual University Graduation Ceremony',
                'event_type' => 'ceremony',
                'start_date' => '2026-11-20',
                'end_date' => '2026-11-21',
                'is_all_day' => true,
                'target_audience' => 'all',
                'is_holiday' => false,
                'description' => 'Conferment of degrees, diplomas, and postgraduate awards at the University Freedom Square.',
            ],
            [
                'academic_year_id' => $currentYear->id,
                'semester_id' => $currentSem1->id,
                'title' => 'End of Semester 1 Teaching & Lectures',
                'event_type' => 'lecture_period',
                'start_date' => '2026-11-27',
                'end_date' => '2026-11-27',
                'is_all_day' => true,
                'target_audience' => 'all',
                'is_holiday' => false,
                'description' => 'Formal conclusion of classroom instruction. Revision weekend commences.',
            ],
            [
                'academic_year_id' => $currentYear->id,
                'semester_id' => $currentSem1->id,
                'title' => 'Semester 1 Final Examinations Period',
                'event_type' => 'examination',
                'start_date' => '2026-11-30',
                'end_date' => '2026-12-18',
                'is_all_day' => true,
                'target_audience' => 'students',
                'is_holiday' => false,
                'description' => 'Main end-of-semester examinations according to university timetable committee schedule.',
            ],
            [
                'academic_year_id' => $currentYear->id,
                'semester_id' => $currentSem1->id,
                'title' => 'Examination Marks & Grade Submission Deadline',
                'event_type' => 'governance',
                'start_date' => '2027-01-08',
                'end_date' => '2027-01-08',
                'is_all_day' => true,
                'target_audience' => 'lecturers',
                'is_holiday' => false,
                'description' => 'Academic staff grading portal locks. Final departmental board reviews commence.',
            ],
            [
                'academic_year_id' => $currentYear->id,
                'semester_id' => $currentSem2->id,
                'title' => 'Commencement of Semester 2 Lectures',
                'event_type' => 'lecture_period',
                'start_date' => '2027-01-11',
                'end_date' => '2027-01-11',
                'is_all_day' => true,
                'target_audience' => 'all',
                'is_holiday' => false,
                'description' => 'Beginning of Semester 2 classes across all academic programmes.',
            ],
        ];

        foreach ($events as $eventData) {
            AcademicEvent::firstOrCreate(
                [
                    'academic_year_id' => $eventData['academic_year_id'],
                    'title' => $eventData['title'],
                    'start_date' => $eventData['start_date'],
                ],
                $eventData
            );
        }
    }
}
