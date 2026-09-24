<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Faculty;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructurePhase1Test extends TestCase
{
    use RefreshDatabase;

    protected University $university;

    protected function setUp(): void
    {
        parent::setUp();

        $this->university = University::factory()->create([
            'name' => 'Apex University',
            'code' => 'APEX',
        ]);
    }

    public function test_can_render_campus_and_faculty_indexes(): void
    {
        Campus::factory()->create(['university_id' => $this->university->id]);

        $this->get(route('academic.campuses.index'))
            ->assertStatus(200)
            ->assertSee('University Campuses');

        $this->get(route('academic.faculties.index'))
            ->assertStatus(200)
            ->assertSee('Academic Faculties');

        $this->get(route('academic.university.edit'))
            ->assertStatus(200)
            ->assertSee('Root Institution Governance Profile');
    }

    public function test_can_create_a_campus_successfully(): void
    {
        $payload = [
            'university_id' => $this->university->id,
            'name' => 'Kampala Central Campus',
            'code' => 'KLA',
            'location' => 'Kampala Central',
            'is_main_campus' => 1,
            'status' => 'active',
        ];

        $response = $this->post(route('academic.campuses.store'), $payload);

        $response->assertRedirect(route('academic.campuses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('campuses', [
            'university_id' => $this->university->id,
            'code' => 'KLA',
            'is_main_campus' => true,
        ]);
    }

    public function test_setting_main_campus_unsets_previous_main_campus(): void
    {
        $firstCampus = Campus::factory()->create([
            'university_id' => $this->university->id,
            'is_main_campus' => true,
        ]);

        $this->assertTrue($firstCampus->fresh()->is_main_campus);

        $payload = [
            'university_id' => $this->university->id,
            'name' => 'New Primary Campus',
            'code' => 'NEW-MAIN',
            'is_main_campus' => 1,
            'status' => 'active',
        ];

        $this->post(route('academic.campuses.store'), $payload);

        $this->assertFalse($firstCampus->fresh()->is_main_campus);
        $this->assertDatabaseHas('campuses', [
            'code' => 'NEW-MAIN',
            'is_main_campus' => true,
        ]);
    }

    public function test_validation_prevents_duplicate_campus_code(): void
    {
        Campus::factory()->create([
            'university_id' => $this->university->id,
            'code' => 'MAIN',
        ]);

        $payload = [
            'university_id' => $this->university->id,
            'name' => 'Another Campus With Same Code',
            'code' => 'MAIN',
            'status' => 'active',
        ];

        $response = $this->post(route('academic.campuses.store'), $payload);
        $response->assertSessionHasErrors('code');
    }

    public function test_can_create_faculty_under_campus(): void
    {
        $campus = Campus::factory()->create(['university_id' => $this->university->id]);
        $dean = User::factory()->create(['name' => 'Dr. Jane Dean']);

        $payload = [
            'campus_id' => $campus->id,
            'name' => 'Faculty of Engineering',
            'code' => 'FOE',
            'dean_user_id' => $dean->id,
            'description' => 'Civil, Mechanical, and Electrical Engineering.',
            'status' => 'active',
        ];

        $response = $this->post(route('academic.faculties.store'), $payload);

        $response->assertRedirect(route('academic.faculties.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('faculties', [
            'campus_id' => $campus->id,
            'code' => 'FOE',
            'dean_user_id' => $dean->id,
        ]);
    }

    public function test_validation_prevents_duplicate_faculty_code_in_same_campus(): void
    {
        $campus = Campus::factory()->create(['university_id' => $this->university->id]);

        Faculty::factory()->create([
            'campus_id' => $campus->id,
            'code' => 'FST',
        ]);

        $payload = [
            'campus_id' => $campus->id,
            'name' => 'Duplicate Code Faculty',
            'code' => 'FST',
            'status' => 'active',
        ];

        $response = $this->post(route('academic.faculties.store'), $payload);
        $response->assertSessionHasErrors('code');
    }

    public function test_cannot_delete_campus_with_associated_faculties(): void
    {
        $campus = Campus::factory()->create(['university_id' => $this->university->id]);
        Faculty::factory()->create(['campus_id' => $campus->id]);

        $response = $this->delete(route('academic.campuses.destroy', $campus));

        $response->assertRedirect(route('academic.campuses.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('campuses', ['id' => $campus->id]);
    }

    public function test_can_update_university_profile(): void
    {
        $payload = [
            'name' => 'Apex Premier University of Uganda',
            'code' => 'APEX-PREMIER',
            'email' => 'admin@apex-premier.ac.ug',
            'website' => 'https://apex-premier.ac.ug',
        ];

        $response = $this->put(route('academic.university.update', $this->university), $payload);

        $response->assertRedirect(route('academic.university.edit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('universities', [
            'id' => $this->university->id,
            'name' => 'Apex Premier University of Uganda',
            'code' => 'APEX-PREMIER',
        ]);
    }
}
