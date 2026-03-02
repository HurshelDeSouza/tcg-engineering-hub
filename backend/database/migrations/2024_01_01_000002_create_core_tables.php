<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Projects
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('client_name');
            $table->enum('status', ['draft', 'discovery', 'execution', 'delivered'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });

        // Artifacts
        Schema::create('artifacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'strategic_alignment',
                'big_picture',
                'domain_breakdown',
                'module_matrix',
                'module_engineering',
                'system_architecture',
                'phase_scope',
            ]);
            $table->enum('status', ['not_started', 'in_progress', 'blocked', 'done'])->default('not_started');
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('content_json')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'type']); // one artifact per type per project
        });

        // Modules
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('domain');
            $table->string('name');
            $table->enum('status', ['draft', 'validated', 'ready_for_build'])->default('draft');
            $table->text('objective')->nullable();
            $table->json('inputs')->nullable();         // array
            $table->text('data_structure')->nullable();
            $table->text('logic_rules')->nullable();
            $table->json('outputs')->nullable();        // array
            $table->text('responsibility')->nullable();
            $table->text('failure_scenarios')->nullable();
            $table->text('audit_trail_requirements')->nullable();
            $table->json('dependencies')->nullable();   // array of module_ids
            $table->string('version_note')->nullable();
            $table->timestamps();
        });

        // Audit Events
        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_user_id')->constrained('users');
            $table->enum('entity_type', ['project', 'artifact', 'module']);
            $table->unsignedBigInteger('entity_id');
            $table->enum('action', ['created', 'updated', 'status_changed', 'validated', 'completed']);
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('artifacts');
        Schema::dropIfExists('projects');
    }
};
