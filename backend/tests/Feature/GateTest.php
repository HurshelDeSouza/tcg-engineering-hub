<?php

namespace Tests\Feature;

use App\Models\Artifact;
use App\Models\Module;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test 1: Gate 1 — domain_breakdown cannot be done if big_picture not done.
 */
class GateTest extends TestCase
{
    use RefreshDatabase;

    private function makeProjectWithArtifacts(string $bigPictureStatus = 'in_progress'): array
    {
        $admin   = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'name'        => 'Test Project',
            'client_name' => 'Client',
            'status'      => 'discovery',
            'created_by'  => $admin->id,
        ]);

        $bigPicture = Artifact::create([
            'project_id'   => $project->id,
            'type'         => 'big_picture',
            'status'       => $bigPictureStatus,
            'content_json' => [],
        ]);

        $domainBreakdown = Artifact::create([
            'project_id'   => $project->id,
            'type'         => 'domain_breakdown',
            'status'       => 'in_progress',
            'content_json' => [],
        ]);

        return [$admin, $project, $bigPicture, $domainBreakdown];
    }

    /** @test */
    public function gate1_blocks_domain_breakdown_done_when_big_picture_not_done()
    {
        [$admin, $project, $bigPicture, $domainBreakdown] = $this->makeProjectWithArtifacts('in_progress');

        $response = $this->actingAs($admin)
            ->patchJson("/api/v1/projects/{$project->id}/artifacts/{$domainBreakdown->id}/status", [
                'status' => 'done',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', fn($message) => str_contains($message, 'Gate 1'));
    }

    /** @test */
    public function gate1_allows_domain_breakdown_done_when_big_picture_is_done()
    {
        [$admin, $project, $bigPicture, $domainBreakdown] = $this->makeProjectWithArtifacts('done');
        $bigPicture->update(['completed_at' => now()]);

        $response = $this->actingAs($admin)
            ->patchJson("/api/v1/projects/{$project->id}/artifacts/{$domainBreakdown->id}/status", [
                'status' => 'done',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'done');
    }

    /** @test */
    public function gate3_blocks_system_architecture_done_without_enough_validated_modules()
    {
        $admin   = User::factory()->create(['role' => 'admin']);
        $project = Project::create(['name' => 'P', 'client_name' => 'C', 'status' => 'discovery', 'created_by' => $admin->id]);

        $sysArch = Artifact::create(['project_id' => $project->id, 'type' => 'system_architecture', 'status' => 'in_progress', 'content_json' => []]);

        // Only 1 validated module (need 3)
        Module::create(['project_id' => $project->id, 'domain' => 'Auth', 'name' => 'Auth Module', 'status' => 'validated', 'objective' => 'x', 'inputs' => ['a'], 'outputs' => ['b'], 'responsibility' => 'team']);

        $response = $this->actingAs($admin)
            ->patchJson("/api/v1/projects/{$project->id}/artifacts/{$sysArch->id}/status", ['status' => 'done']);

        $response->assertStatus(422)
            ->assertJsonPath('message', fn($message) => str_contains($message, 'Gate 3'));
    }
}
