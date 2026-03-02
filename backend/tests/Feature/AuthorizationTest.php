<?php

namespace Tests\Feature;

use App\Models\Artifact;
use App\Models\Module;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test 4: Authorization — viewer cannot edit modules/artifacts.
 */
class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function setupProjectWithEntities(): array
    {
        $admin   = User::factory()->create(['role' => 'admin']);
        $viewer  = User::factory()->create(['role' => 'viewer']);
        $project = Project::create(['name' => 'P', 'client_name' => 'C', 'status' => 'draft', 'created_by' => $admin->id]);

        $artifact = Artifact::create(['project_id' => $project->id, 'type' => 'big_picture', 'status' => 'not_started', 'content_json' => []]);
        $module   = Module::create(['project_id' => $project->id, 'domain' => 'Auth', 'name' => 'M', 'status' => 'draft']);

        return [$admin, $viewer, $project, $artifact, $module];
    }

    /** @test */
    public function viewer_cannot_update_artifact()
    {
        [$admin, $viewer, $project, $artifact, $module] = $this->setupProjectWithEntities();

        $this->actingAs($viewer)
            ->putJson("/api/v1/projects/{$project->id}/artifacts/{$artifact->id}", [
                'content_json' => ['ecosystem_vision' => 'Hacked'],
            ])
            ->assertStatus(403);
    }

    /** @test */
    public function viewer_cannot_update_module()
    {
        [$admin, $viewer, $project, $artifact, $module] = $this->setupProjectWithEntities();

        $this->actingAs($viewer)
            ->putJson("/api/v1/projects/{$project->id}/modules/{$module->id}", [
                'objective' => 'Hacked',
            ])
            ->assertStatus(403);
    }

    /** @test */
    public function viewer_can_read_projects_and_artifacts()
    {
        [$admin, $viewer, $project, $artifact, $module] = $this->setupProjectWithEntities();

        $this->actingAs($viewer)->getJson("/api/v1/projects")->assertOk();
        $this->actingAs($viewer)->getJson("/api/v1/projects/{$project->id}")->assertOk();
        $this->actingAs($viewer)->getJson("/api/v1/projects/{$project->id}/artifacts")->assertOk();
    }

    /** @test */
    public function engineer_can_update_modules_but_not_artifacts()
    {
        $engineer = User::factory()->create(['role' => 'engineer']);
        [$admin, $viewer, $project, $artifact, $module] = $this->setupProjectWithEntities();

        // Engineer can update modules
        $this->actingAs($engineer)
            ->putJson("/api/v1/projects/{$project->id}/modules/{$module->id}", [
                'objective' => 'Updated by engineer',
            ])
            ->assertOk();

        // Engineer cannot update artifacts
        $this->actingAs($engineer)
            ->putJson("/api/v1/projects/{$project->id}/artifacts/{$artifact->id}", [
                'content_json' => [],
            ])
            ->assertStatus(403);
    }
}
