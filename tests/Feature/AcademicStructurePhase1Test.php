<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Faculty;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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

        $this->get(route('campus.list'))
            ->assertStatus(200)
            ->assertSee('University Campuses');

        $this->get(route('faculty.list'))
            ->assertStatus(200)
            ->assertSee('Academic Faculties');

        $this->get(route('university.edit'))
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

        $response = $this->post(route('campus.store'), $payload);

        $response->assertRedirect(route('campus.list'));
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

        $this->post(route('campus.store'), $payload);

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

        $response = $this->post(route('campus.store'), $payload);
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

        $response = $this->post(route('faculty.store'), $payload);

        $response->assertRedirect(route('faculty.list'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('faculties', [
            'campus_id' => $campus->id,
            'code' => 'FOE',
            'dean_user_id' => $dean->id,
        ]);
    }

    public function test_creating_faculty_from_campus_screen_redirects_to_campus_show(): void
    {
        $campus = Campus::factory()->create(['university_id' => $this->university->id]);

        $payload = [
            'campus_id' => $campus->id,
            'name' => 'Faculty of Law',
            'code' => 'FOL',
            'status' => 'active',
            'from_campus' => '1',
        ];

        $response = $this->post(route('faculty.store'), $payload);

        $response->assertRedirect(route('campus.show', $campus));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('faculties', [
            'campus_id' => $campus->id,
            'code' => 'FOL',
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

        $response = $this->post(route('faculty.store'), $payload);
        $response->assertSessionHasErrors('code');
    }

    public function test_cannot_delete_campus_with_associated_faculties(): void
    {
        $campus = Campus::factory()->create(['university_id' => $this->university->id]);
        Faculty::factory()->create(['campus_id' => $campus->id]);

        $response = $this->delete(route('campus.delete', $campus));

        $response->assertRedirect(route('campus.list'));
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

        $response = $this->put(route('university.update', $this->university), $payload);

        $response->assertRedirect(route('university.edit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('universities', [
            'id' => $this->university->id,
            'name' => 'Apex Premier University of Uganda',
            'code' => 'APEX-PREMIER',
        ]);

        // Verify the edit page loads the updated university without creating duplicate records
        $editResponse = $this->get(route('university.edit'));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Apex Premier University of Uganda');
        $editResponse->assertSee('APEX-PREMIER');
        $this->assertEquals(1, University::count());
    }

    public function test_can_upload_and_update_institution_logo(): void
    {
        Storage::fake('public');

        $logoFile = UploadedFile::fake()->image('crest.png', 300, 300);

        $payload = [
            'name' => $this->university->name,
            'code' => $this->university->code,
            'logo' => $logoFile,
        ];

        $response = $this->put(route('university.update', $this->university), $payload);

        $response->assertRedirect(route('university.edit'));
        $response->assertSessionHas('success');

        $freshUniversity = $this->university->fresh();
        $this->assertNotNull($freshUniversity->logo_path);
        Storage::disk('public')->assertExists($freshUniversity->logo_path);
        $this->assertTrue($freshUniversity->hasCustomLogo());
        $this->assertStringContainsString('storage/logos/', $freshUniversity->logo_url);

        // Verify the edit page and layouts render the uploaded logo and favicon
        $editResponse = $this->get(route('university.edit'));
        $editResponse->assertStatus(200);
        $editResponse->assertSee($freshUniversity->logo_url);
    }

    public function test_can_remove_institution_logo(): void
    {
        Storage::fake('public');

        // First upload a logo
        $logoFile = UploadedFile::fake()->image('crest.png', 300, 300);
        $storedPath = $logoFile->store('logos', 'public');
        $this->university->update(['logo_path' => $storedPath]);

        Storage::disk('public')->assertExists($storedPath);

        // Send update with remove_logo = 1
        $payload = [
            'name' => $this->university->name,
            'code' => $this->university->code,
            'remove_logo' => 1,
        ];

        $response = $this->put(route('university.update', $this->university), $payload);

        $response->assertRedirect(route('university.edit'));
        $response->assertSessionHas('success');

        $freshUniversity = $this->university->fresh();
        $this->assertNull($freshUniversity->logo_path);
        Storage::disk('public')->assertMissing($storedPath);
        $this->assertFalse($freshUniversity->hasCustomLogo());
        $this->assertStringContainsString('AdminLTELogo.png', $freshUniversity->logo_url);
    }

    public function test_rejects_invalid_institution_logo(): void
    {
        Storage::fake('public');

        $invalidFile = UploadedFile::fake()->create('contract.pdf', 500, 'application/pdf');

        $payload = [
            'name' => $this->university->name,
            'code' => $this->university->code,
            'logo' => $invalidFile,
        ];

        $response = $this->put(route('university.update', $this->university), $payload);

        $response->assertSessionHasErrors('logo');
    }

    public function test_university_current_safely_resolves_and_recovers_from_corrupted_cache(): void
    {
        University::clearCurrentCache();

        $current = University::current();
        $this->assertInstanceOf(University::class, $current);
        $this->assertEquals($this->university->id, $current->id);

        // Simulate corrupted legacy cache data
        Cache::put('root_university', 'invalid_serialized_data');
        University::clearCurrentCache();

        $recovered = University::current();
        $this->assertInstanceOf(University::class, $recovered);
        $this->assertEquals($this->university->id, $recovered->id);

        // Update the university and verify cache clears and returns fresh data
        $this->university->update(['name' => 'Refreshed University Name']);
        $updated = University::current();
        $this->assertInstanceOf(University::class, $updated);
        $this->assertEquals('Refreshed University Name', $updated->name);
    }
}
