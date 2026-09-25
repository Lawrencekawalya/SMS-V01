<?php

namespace Tests\Feature;

use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicEventTest extends TestCase
{
    use RefreshDatabase;

    protected AcademicYear $year;

    protected Semester $semester;

    protected function setUp(): void
    {
        parent::setUp();

        University::factory()->create();

        $this->year = AcademicYear::factory()->create([
            'name' => '2026/2027',
            'is_current' => true,
        ]);

        $this->semester = Semester::factory()->create([
            'academic_year_id' => $this->year->id,
            'name' => 'Semester 1',
            'is_active' => true,
        ]);
    }

    public function test_academic_calendar_index_displays_events_table_and_calendar(): void
    {
        $event = AcademicEvent::factory()->create([
            'academic_year_id' => $this->year->id,
            'semester_id' => $this->semester->id,
            'title' => 'Special Convocation and Matriculation',
            'event_type' => 'ceremony',
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-16',
        ]);

        $response = $this->get(route('academic.academic-years.index'));

        $response->assertStatus(200);
        $response->assertSee('Scheduled Academic Events & University Almanac', false);
        $response->assertSee('events-table');
        $response->assertSee('academic-fullcalendar');
        $response->assertSee('Special Convocation and Matriculation');
        $response->assertSee('Ceremony');
    }

    public function test_dedicated_events_page_displays_events_table_and_calendar(): void
    {
        $event = AcademicEvent::factory()->create([
            'academic_year_id' => $this->year->id,
            'semester_id' => $this->semester->id,
            'title' => '21st Annual Convocation Ceremony',
            'event_type' => 'ceremony',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-21',
        ]);

        $response = $this->get(route('academic.events.index'));

        $response->assertStatus(200);
        $response->assertSee('Scheduled Academic Events & University Almanac', false);
        $response->assertSee('events-table');
        $response->assertSee('academic-fullcalendar');
        $response->assertSee('21st Annual Convocation Ceremony');
    }

    public function test_navbar_displays_events_notification_dropdown_with_badge_and_see_all_link(): void
    {
        AcademicEvent::factory()->create([
            'academic_year_id' => $this->year->id,
            'semester_id' => $this->semester->id,
            'title' => 'End of Semester Examinations',
            'start_date' => '2026-11-30',
        ]);

        $response = $this->get(route('academic.events.index'));

        $response->assertStatus(200);
        $response->assertSee('navbar-badge');
        $response->assertSee('See All Events &amp; Almanac', false);
        $response->assertSee(route('academic.events.index'));
    }

    public function test_can_fetch_fullcalendar_json_feed(): void
    {
        $event = AcademicEvent::factory()->create([
            'academic_year_id' => $this->year->id,
            'semester_id' => $this->semester->id,
            'title' => 'End of Semester Final Examinations',
            'event_type' => 'examination',
            'start_date' => '2026-11-30',
            'end_date' => '2026-12-18',
        ]);

        $response = $this->getJson(route('academic.events.feed'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => (string) $event->id,
            'title' => 'End of Semester Final Examinations',
            'start' => '2026-11-30',
            'end' => '2026-12-19', // exclusive end
        ]);
    }

    public function test_can_schedule_academic_event(): void
    {
        $payload = [
            'academic_year_id' => $this->year->id,
            'semester_id' => $this->semester->id,
            'title' => 'Senate Approval of Results',
            'event_type' => 'governance',
            'target_audience' => 'lecturers',
            'start_date' => '2027-01-15',
            'is_all_day' => '1',
            'is_holiday' => '0',
            'description' => 'Senate board meets to approve semester grades.',
        ];

        $response = $this->post(route('academic.events.store'), $payload);

        $response->assertRedirect(route('academic.academic-years.index'));
        $this->assertDatabaseHas('academic_events', [
            'title' => 'Senate Approval of Results',
            'event_type' => 'governance',
            'academic_year_id' => $this->year->id,
            'target_audience' => 'lecturers',
        ]);
    }

    public function test_can_update_scheduled_academic_event(): void
    {
        $event = AcademicEvent::factory()->create([
            'academic_year_id' => $this->year->id,
            'semester_id' => $this->semester->id,
            'title' => 'Initial Title',
            'event_type' => 'academic_deadline',
            'start_date' => '2026-10-01',
        ]);

        $payload = [
            'academic_year_id' => $this->year->id,
            'semester_id' => $this->semester->id,
            'title' => 'Extended Course Registration Deadline',
            'event_type' => 'academic_deadline',
            'target_audience' => 'students',
            'start_date' => '2026-10-10',
            'is_all_day' => '1',
            'is_holiday' => '0',
        ];

        $response = $this->put(route('academic.events.update', $event), $payload);

        $response->assertRedirect(route('academic.academic-years.index'));
        $updatedEvent = $event->fresh();
        $this->assertEquals('Extended Course Registration Deadline', $updatedEvent->title);
        $this->assertEquals('2026-10-10', $updatedEvent->start_date->format('Y-m-d'));
        $this->assertEquals('students', $updatedEvent->target_audience);
    }

    public function test_can_delete_scheduled_academic_event(): void
    {
        $event = AcademicEvent::factory()->create([
            'academic_year_id' => $this->year->id,
        ]);

        $response = $this->delete(route('academic.events.destroy', $event));

        $response->assertRedirect(route('academic.academic-years.index'));
        $this->assertDatabaseMissing('academic_events', [
            'id' => $event->id,
        ]);
    }

    public function test_event_end_date_must_be_greater_than_or_equal_to_start_date(): void
    {
        $response = $this->post(route('academic.events.store'), [
            'academic_year_id' => $this->year->id,
            'title' => 'Invalid Event Dates',
            'event_type' => 'examination',
            'target_audience' => 'all',
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-10', // Before start_date!
        ]);

        $response->assertSessionHasErrors(['end_date']);
    }
}
