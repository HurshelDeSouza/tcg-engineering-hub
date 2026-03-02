<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test 2: Module validation rule — cannot validate if missing required fields.
 */
class ModuleValidationTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdminAndProject(): array
    {
        $admin   = User::factory()->create(['role' => 'admin']);
        $project = Project::create(['name' => 'P', 'client_name' => 'C', 'status' => 'draft', 'created_by' => $admin->id]);
        return [$admin, $project];
    }

    /** @test */
    public function cannot_validate_module_with_missing_required_fields()
    {
        [$admin, $project] = $this->makeAdminAndProject();

        // Module with empty objective and no inputs/outputs
        $module = Module::create([
            'project_id'     => $project->id,
            'domain'         => 'Auth',
            'name'           => 'Incomplete Module',
            'status'         => 'draft',
            'objective'      => '',   // missing
            'inputs'         => [],   // missing
            'outputs'        => [],   // missing
            'responsibility' => '',   // missing
        ]);

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/projects/{$project->id}/modules/{$module->id}/validate");

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors']);
    }

    /** @test */
    public function can_validate_module_with_all_required_fields()
    {
        [$admin, $project] = $this->makeAdminAndProject();

        $module = Module::create([
            'project_id'     => $project->id,
            'domain'         => 'Auth',
            'name'           => 'Auth Module',
            'status'         => 'draft',
            'objective'      => 'Handle authentication',
            'inputs'         => ['credentials'],
            'outputs'        => ['session_token'],
            'responsibility' => 'Backend team',
        ]);

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/projects/{$project->id}/modules/{$module->id}/validate");

        $response->assertOk()
            ->assertJsonPath('data.status', 'validated');
    }
}
