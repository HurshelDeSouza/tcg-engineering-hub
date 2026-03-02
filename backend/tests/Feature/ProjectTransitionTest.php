<?php

namespace Tests\Feature;

use App\Models\Artifact;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test 3: Project cannot move discovery→execution without required artifact statuses.
 */
class ProjectTransitionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cannot_transition_to_execution_without_required_artifacts_done()
    {
        $admin   = User::factory()->create(['role' => 'admin']);
        $project = Project::create(['name' => 'P', 'client_name' => 'C', 'status' => 'discovery', 'created_by' => $admin->id]);

        // Only strategic_alignment done, missing others
        Artifact::create(['project_id' => $project->id, 'type' => 'strategic_alignment', 'status' => 'done', 'content_json' => []]);
        Artifact::create(['project_id' => $project->id, 'type' => 'big_picture', 'status' => 'in_progress', 'content_json' => []]);

        $response = $this->actingAs($admin)
            ->patchJson("/api/v1/projects/{$project->id}/status", ['status' => 'execution']);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', fn($message) => str_contains($message, 'Gate 4'));
    }

    /** @test */
    public function can_transition_to_execution_when_all_required_artifacts_done()
    {
        $admin   = User::factory()->create(['role' => 'admin']);
        $project = Project::create(['name' => 'P', 'client_name' => 'C', 'status' => 'discovery', 'created_by' => $admin->id]);

        $required = ['strategic_alignment', 'big_picture', 'domain_breakdown', 'module_matrix'];
        foreach ($required as $type) {
            Artifact::create(['project_id' => $project->id, 'type' => $type, 'status' => 'done', 'completed_at' => now(), 'content_json' => []]);
        }

        $response = $this->actingAs($admin)
            ->patchJson("/api/v1/projects/{$project->id}/status", ['status' => 'execution']);

        $response->assertOk()
            ->assertJsonPath('data.status', 'execution');
    }
}
