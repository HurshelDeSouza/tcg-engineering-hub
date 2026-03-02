<?php

namespace Database\Seeders;

use App\Models\Artifact;
use App\Models\Module;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo users
        $admin = User::create([
            'name'     => 'Admin TCG',
            'email'    => 'admin@tcg.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        $pm = User::create([
            'name'     => 'Project Manager',
            'email'    => 'pm@tcg.com',
            'password' => Hash::make('password'),
            'role'     => 'pm',
        ]);

        $engineer = User::create([
            'name'     => 'Engineer',
            'email'    => 'engineer@tcg.com',
            'password' => Hash::make('password'),
            'role'     => 'engineer',
        ]);

        User::create([
            'name'     => 'Viewer',
            'email'    => 'viewer@tcg.com',
            'password' => Hash::make('password'),
            'role'     => 'viewer',
        ]);

        // Create a demo project in discovery
        $project = Project::create([
            'name'        => 'E-Commerce Platform Rebuild',
            'client_name' => 'Acme Corp',
            'status'      => 'discovery',
            'created_by'  => $admin->id,
        ]);

        // Create additional demo projects with different statuses
        $project2 = Project::create([
            'name'        => 'Mobile Banking App',
            'client_name' => 'FinTech Solutions',
            'status'      => 'execution',
            'created_by'  => $pm->id,
        ]);

        $project3 = Project::create([
            'name'        => 'Healthcare Portal',
            'client_name' => 'MediCare Inc',
            'status'      => 'delivered',
            'created_by'  => $admin->id,
        ]);

        Project::create([
            'name'        => 'Inventory Management System',
            'client_name' => 'Logistics Co',
            'status'      => 'draft',
            'created_by'  => $pm->id,
        ]);

        // Seed all 7 artifact types
        $artifactTypes = [
            'strategic_alignment',
            'big_picture',
            'domain_breakdown',
            'module_matrix',
            'module_engineering',
            'system_architecture',
            'phase_scope',
        ];

        foreach ($artifactTypes as $type) {
            $artifact = Artifact::create([
                'project_id'    => $project->id,
                'type'          => $type,
                'status'        => 'not_started',
                'owner_user_id' => $pm->id,
                'content_json'  => $this->getSampleContent($type),
            ]);

            // Mark first two as done so gates can be demonstrated
            if (in_array($type, ['strategic_alignment', 'big_picture'])) {
                $artifact->update(['status' => 'done', 'completed_at' => now()]);
            }
        }

        // Seed some modules (3 validated so gate 3 can be tested)
        $domains = ['Auth', 'Catalog', 'Cart'];
        foreach ($domains as $i => $domain) {
            Module::create([
                'project_id'      => $project->id,
                'domain'          => $domain,
                'name'            => "{$domain} Module",
                'status'          => 'validated',
                'objective'       => "Handle {$domain} operations for the platform",
                'inputs'          => ['user_request', 'session_data'],
                'outputs'         => ['processed_result', 'status_code'],
                'responsibility'  => 'Engineering team',
                'data_structure'  => 'Relational DB tables with Redis cache layer',
                'logic_rules'     => 'Standard CRUD with soft deletes',
                'failure_scenarios' => 'Timeout, DB connection error, validation failure',
                'audit_trail_requirements' => 'Log all state changes with actor',
                'version_note'    => 'v1.0',
            ]);
        }
    }

    private function getSampleContent(string $type): array
    {
        return match($type) {
            'strategic_alignment' => [
                'transformation'       => 'Migrate legacy monolith to modular SaaS',
                'supported_decisions'  => ['Build vs Buy', 'Tech Stack Selection'],
                'measurable_success'   => [['metric' => 'Uptime', 'target' => '99.9%']],
                'out_of_scope'         => ['Mobile app', 'Third-party integrations v1'],
            ],
            'big_picture' => [
                'ecosystem_vision'  => 'Unified e-commerce ecosystem with microservices',
                'impacted_domains'  => ['Auth', 'Catalog', 'Cart', 'Orders', 'Payments'],
                'success_definition' => 'Platform handles 10k concurrent users with <200ms p95',
            ],
            default => [],
        };
    }
}
