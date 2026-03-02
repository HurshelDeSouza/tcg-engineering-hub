<?php

namespace Tests\Feature;

use App\Models\Artifact;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test content_json validation for different artifact types.
 * Ensures that each artifact type has proper field validation.
 */
class ArtifactContentValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->project = Project::create([
            'name' => 'Test Project',
            'client_name' => 'Client',
            'status' => 'discovery',
            'created_by' => $this->admin->id,
        ]);
    }

    /** @test */
    public function strategic_alignment_requires_transformation()
    {
        $artifact = Artifact::create([
            'project_id' => $this->project->id,
            'type' => 'strategic_alignment',
            'status' => 'in_progress',
            'content_json' => [],
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/projects/{$this->project->id}/artifacts/{$artifact->id}", [
                'content_json' => [
                    'supported_decisions' => ['Decision 1'],
                    'measurable_success' => [['metric' => 'Uptime', 'target' => '99%']],
                ],
            ]);

        // Should fail because transformation is missing
        $response->assertStatus(422)
            ->assertJsonPath('errors.content_json', fn($errors) => 
                is_array($errors) && count($errors) > 0
            );
    }

    /** @test */
    public function strategic_alignment_accepts_valid_content()
    {
        $artifact = Artifact::create([
            'project_id' => $this->project->id,
            'type' => 'strategic_alignment',
            'status' => 'in_progress',
            'content_json' => [],
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/projects/{$this->project->id}/artifacts/{$artifact->id}", [
                'content_json' => [
                    'transformation' => 'Migrate to cloud-native architecture',
                    'supported_decisions' => ['Build vs Buy', 'Tech Stack'],
                    'measurable_success' => [
                        ['metric' => 'Uptime', 'target' => '99.9%'],
                        ['metric' => 'Response Time', 'target' => '<200ms'],
                    ],
                    'out_of_scope' => ['Mobile app'],
                ],
            ]);

        $response->assertOk();
    }

    /** @test */
    public function big_picture_requires_ecosystem_vision()
    {
        $artifact = Artifact::create([
            'project_id' => $this->project->id,
            'type' => 'big_picture',
            'status' => 'in_progress',
            'content_json' => [],
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/projects/{$this->project->id}/artifacts/{$artifact->id}", [
                'content_json' => [
                    'impacted_domains' => ['Auth', 'Catalog'],
                    // Missing ecosystem_vision and success_definition
                ],
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function big_picture_accepts_valid_content()
    {
        $artifact = Artifact::create([
            'project_id' => $this->project->id,
            'type' => 'big_picture',
            'status' => 'in_progress',
            'content_json' => [],
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/projects/{$this->project->id}/artifacts/{$artifact->id}", [
                'content_json' => [
                    'ecosystem_vision' => 'Unified e-commerce platform with microservices',
                    'impacted_domains' => ['Auth', 'Catalog', 'Cart', 'Orders'],
                    'success_definition' => 'Platform handles 10k concurrent users',
                ],
            ]);

        $response->assertOk();
    }

    /** @test */
    public function domain_breakdown_requires_domains_array()
    {
        $artifact = Artifact::create([
            'project_id' => $this->project->id,
            'type' => 'domain_breakdown',
            'status' => 'in_progress',
            'content_json' => [],
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/projects/{$this->project->id}/artifacts/{$artifact->id}", [
                'content_json' => [
                    'domains' => [], // Empty array should fail
                ],
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function domain_breakdown_accepts_valid_domains()
    {
        $artifact = Artifact::create([
            'project_id' => $this->project->id,
            'type' => 'domain_breakdown',
            'status' => 'in_progress',
            'content_json' => [],
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/projects/{$this->project->id}/artifacts/{$artifact->id}", [
                'content_json' => [
                    'domains' => [
                        [
                            'name' => 'Authentication',
                            'objective' => 'Handle user authentication and authorization',
                            'owner_user_id' => $this->admin->id,
                        ],
                        [
                            'name' => 'Catalog',
                            'objective' => 'Manage product catalog',
                        ],
                    ],
                ],
            ]);

        $response->assertOk();
    }

    /** @test */
    public function system_architecture_requires_all_core_fields()
    {
        $artifact = Artifact::create([
            'project_id' => $this->project->id,
            'type' => 'system_architecture',
            'status' => 'in_progress',
            'content_json' => [],
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/projects/{$this->project->id}/artifacts/{$artifact->id}", [
                'content_json' => [
                    'auth_model' => 'JWT',
                    // Missing api_style and data_model_notes
                ],
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function empty_content_json_is_allowed_for_drafts()
    {
        $artifact = Artifact::create([
            'project_id' => $this->project->id,
            'type' => 'strategic_alignment',
            'status' => 'not_started',
            'content_json' => [],
        ]);

        // Empty content should be allowed (for drafts)
        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/projects/{$this->project->id}/artifacts/{$artifact->id}", [
                'content_json' => [],
            ]);

        $response->assertOk();
    }
}
