<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicCalendarPhase3Test extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_academic_calendar_index_with_active_session_and_datatable(): void
    {
        $year = AcademicYear::factory()->create([
            'name' => '2026/2027',
            'is_current' => true,
        ]);

        $semester = Semester::factory()->create([
            'academic_year_id' => $year->id,
            'name' => 'Semester 1',
            'is_active' => true,
        ]);

        $response = $this->get(route('academic.academic-years.index'));

        $response->assertStatus(200);
        $response->assertSee('Academic Calendar & Sessions');
        $response->assertSee('Current University Session: 2026/2027');
        $response->assertSee('years-table');
        $response->assertSee('semesters-table');
        $response->assertSee($year->name);
        $response->assertSee($semester->name);
    }

    public function test_can_create_academic_year(): void
    {
        $payload = [
            'name' => '2027/2028',
            'start_date' => '2027-08-16',
            'end_date' => '2028-06-25',
            'is_current' => 1,
            'description' => 'Upcoming academic year session.',
        ];

        $response = $this->post(route('academic.academic-years.store'), $payload);

        $response->assertRedirect(route('academic.academic-years.index'));
        $this->assertDatabaseHas('academic_years', [
            'name' => '2027/2028',
            'is_current' => true,
        ]);
    }

    public function test_academic_year_validation_enforces_chronological_dates(): void
    {
        // Illogical end_date before start_date
        $payload = [
            'name' => '2027/2028',
            'start_date' => '2027-08-16',
            'end_date' => '2027-01-01',
        ];

        $response = $this->post(route('academic.academic-years.store'), $payload);

        $response->assertSessionHasErrors(['end_date']);
        $this->assertDatabaseMissing('academic_years', ['name' => '2027/2028']);
    }

    public function test_can_set_academic_year_as_current_and_unsets_previous_current(): void
    {
        $firstYear = AcademicYear::factory()->create([
            'name' => '2025/2026',
            'is_current' => true,
        ]);

        $secondYear = AcademicYear::factory()->create([
            'name' => '2026/2027',
            'is_current' => false,
        ]);

        $response = $this->post(route('academic.academic-years.make-current', $secondYear));

        $response->assertSessionHas('success');
        $this->assertTrue($secondYear->fresh()->is_current);
        $this->assertFalse($firstYear->fresh()->is_current);
    }

    public function test_can_create_semester_under_academic_year(): void
    {
        $year = AcademicYear::factory()->create(['name' => '2026/2027']);

        $payload = [
            'academic_year_id' => $year->id,
            'semester_number' => 1,
            'name' => 'Semester 1',
            'start_date' => '2026-08-17',
            'end_date' => '2026-12-18',
            'registration_start_date' => '2026-08-01',
            'registration_end_date' => '2026-09-15',
            'add_drop_deadline' => '2026-09-30',
            'is_active' => 1,
        ];

        $response = $this->post(route('academic.semesters.store'), $payload);

        $response->assertRedirect(route('academic.academic-years.index'));
        $this->assertDatabaseHas('semesters', [
            'academic_year_id' => $year->id,
            'semester_number' => 1,
            'name' => 'Semester 1',
            'is_active' => true,
        ]);
        $this->assertTrue($year->fresh()->is_current);
    }

    public function test_semester_validation_enforces_chronological_dates_and_deadlines(): void
    {
        $year = AcademicYear::factory()->create(['name' => '2026/2027']);

        // End date before start date
        $payloadInvalidDates = [
            'academic_year_id' => $year->id,
            'semester_number' => 1,
            'name' => 'Semester 1',
            'start_date' => '2026-08-17',
            'end_date' => '2026-05-01',
        ];

        $response = $this->post(route('academic.semesters.store'), $payloadInvalidDates);
        $response->assertSessionHasErrors(['end_date']);

        // Registration end date after semester end date
        $payloadInvalidRegistration = [
            'academic_year_id' => $year->id,
            'semester_number' => 1,
            'name' => 'Semester 1',
            'start_date' => '2026-08-17',
            'end_date' => '2026-12-18',
            'registration_start_date' => '2026-08-01',
            'registration_end_date' => '2027-01-15', // Beyond end_date
        ];

        $response2 = $this->post(route('academic.semesters.store'), $payloadInvalidRegistration);
        $response2->assertSessionHasErrors(['registration_end_date']);
    }

    public function test_can_activate_semester_and_unsets_previous_active(): void
    {
        $year = AcademicYear::factory()->create(['is_current' => true]);

        $sem1 = Semester::factory()->create([
            'academic_year_id' => $year->id,
            'semester_number' => 1,
            'is_active' => true,
        ]);

        $sem2 = Semester::factory()->create([
            'academic_year_id' => $year->id,
            'semester_number' => 2,
            'is_active' => false,
        ]);

        $response = $this->post(route('academic.semesters.activate', $sem2));

        $response->assertSessionHas('success');
        $this->assertTrue($sem2->fresh()->is_active);
        $this->assertFalse($sem1->fresh()->is_active);
    }

    public function test_cannot_delete_current_academic_year(): void
    {
        $currentYear = AcademicYear::factory()->create(['is_current' => true]);

        $response = $this->delete(route('academic.academic-years.destroy', $currentYear));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('academic_years', ['id' => $currentYear->id]);
    }

    public function test_cannot_delete_active_semester(): void
    {
        $year = AcademicYear::factory()->create();
        $activeSemester = Semester::factory()->create([
            'academic_year_id' => $year->id,
            'is_active' => true,
        ]);

        $response = $this->delete(route('academic.semesters.destroy', $activeSemester));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('semesters', ['id' => $activeSemester->id]);
    }

    public function test_can_update_academic_year_and_semester(): void
    {
        $year = AcademicYear::factory()->create(['name' => '2026/2027']);
        $semester = Semester::factory()->create([
            'academic_year_id' => $year->id,
            'name' => 'Old Semester 1',
        ]);

        $this->put(route('academic.academic-years.update', $year), [
            'name' => '2026/2027 Updated',
            'start_date' => $year->start_date->format('Y-m-d'),
            'end_date' => $year->end_date->format('Y-m-d'),
        ])->assertRedirect(route('academic.academic-years.index'));

        $this->assertDatabaseHas('academic_years', ['name' => '2026/2027 Updated']);

        $this->put(route('academic.semesters.update', $semester), [
            'academic_year_id' => $year->id,
            'semester_number' => $semester->semester_number,
            'name' => 'New Semester 1 Name',
            'start_date' => $semester->start_date->format('Y-m-d'),
            'end_date' => $semester->end_date->format('Y-m-d'),
        ])->assertRedirect(route('academic.academic-years.index'));

        $this->assertDatabaseHas('semesters', ['name' => 'New Semester 1 Name']);
    }
}
