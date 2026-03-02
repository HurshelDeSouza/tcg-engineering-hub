<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArtifactResource;
use App\Models\Artifact;
use App\Models\Project;
use App\Services\ArtifactGateService;
use App\Services\ArtifactContentValidator;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ArtifactController extends Controller
{
    public function __construct(
        private readonly ArtifactGateService $gateService,
        private readonly ArtifactContentValidator $contentValidator,
        private readonly AuditService $auditService,
    ) {}

    public function index(Project $project)
    {
        $this->authorize('viewAny', Artifact::class);

        $artifacts = $project->artifacts()->with('owner')->get()->map(function ($artifact) {
            $artifact->blocked_reason = $this->gateService->getBlockedReason($artifact);
            return $artifact;
        });

        return ArtifactResource::collection($artifacts);
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('create', Artifact::class);

        $data = $request->validate([
            'type'          => 'required|in:strategic_alignment,big_picture,domain_breakdown,module_matrix,module_engineering,system_architecture,phase_scope',
            'owner_user_id' => 'nullable|exists:users,id',
            'content_json'  => 'nullable|array',
        ]);

        // Validate content_json structure based on artifact type
        if (!empty($data['content_json'])) {
            $this->contentValidator->validate($data['type'], $data['content_json']);
        }

        $artifact = $project->artifacts()->create([
            ...$data,
            'status' => 'not_started',
        ]);

        $this->auditService->logCreated('artifact', $artifact);

        return new ArtifactResource($artifact->load('owner'));
    }

    public function show(Project $project, Artifact $artifact)
    {
        $this->authorize('view', $artifact);
        $this->ensureBelongsToProject($project, $artifact);

        $artifact->blocked_reason = $this->gateService->getBlockedReason($artifact);

        return new ArtifactResource($artifact->load('owner'));
    }

    public function update(Request $request, Project $project, Artifact $artifact)
    {
        $this->authorize('update', $artifact);
        $this->ensureBelongsToProject($project, $artifact);

        $before = $artifact->toArray();

        $data = $request->validate([
            'owner_user_id' => 'nullable|exists:users,id',
            'content_json'  => 'nullable|array',
        ]);

        // Validate content_json structure based on artifact type
        if (isset($data['content_json'])) {
            $this->contentValidator->validate($artifact->type, $data['content_json']);
        }

        $artifact->update($data);

        $this->auditService->logUpdated('artifact', $artifact, $before, $artifact->fresh()->toArray());

        $artifact->blocked_reason = $this->gateService->getBlockedReason($artifact);

        return new ArtifactResource($artifact->load('owner'));
    }

    public function updateStatus(Request $request, Project $project, Artifact $artifact)
    {
        $this->authorize('update', $artifact);
        $this->ensureBelongsToProject($project, $artifact);

        $data = $request->validate([
            'status' => 'required|in:not_started,in_progress,blocked,done',
        ]);

        $newStatus = $data['status'];
        $oldStatus = $artifact->status;

        // Check gates if trying to mark as done
        if ($newStatus === 'done') {
            $artifact->load('project.artifacts', 'project.modules');
            $check = $this->gateService->canMarkDone($artifact);

            if (!$check['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $check['reason'],
                ], 422);
            }

            $artifact->update([
                'status'       => 'done',
                'completed_at' => now(),
            ]);

            $this->auditService->logCompleted('artifact', $artifact->id);
        } else {
            $artifact->update(['status' => $newStatus, 'completed_at' => null]);
            $this->auditService->logStatusChanged('artifact', $artifact->id, $oldStatus, $newStatus);
        }

        $artifact->blocked_reason = $this->gateService->getBlockedReason($artifact->fresh());

        return new ArtifactResource($artifact->fresh()->load('owner'));
    }

    private function ensureBelongsToProject(Project $project, Artifact $artifact): void
    {
        abort_if($artifact->project_id !== $project->id, 404);
    }
}
