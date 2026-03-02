<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ArtifactGateService;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ArtifactGateService $gateService,
        private readonly AuditService $auditService,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::with('creator')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate(15);

        return ProjectResource::collection($projects);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'status'      => 'sometimes|in:draft,discovery,execution,delivered',
        ]);

        $project = Project::create([
            ...$data,
            'created_by' => $request->user()->id,
            'status'     => $data['status'] ?? 'draft',
        ]);

        $this->auditService->logCreated('project', $project);

        return new ProjectResource($project->load('creator'));
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return new ProjectResource($project->load(['creator', 'artifacts.owner', 'modules']));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $before = $project->toArray();

        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'client_name' => 'sometimes|string|max:255',
        ]);

        $project->update($data);

        $this->auditService->logUpdated('project', $project, $before, $project->toArray());

        return new ProjectResource($project->load('creator'));
    }

    public function transition(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'status' => 'required|in:draft,discovery,execution,delivered',
        ]);

        $newStatus = $data['status'];
        $oldStatus = $project->status;

        // Gate 4: discovery → execution requires mandatory artifacts
        if ($oldStatus === 'discovery' && $newStatus === 'execution') {
            $project->load('artifacts');
            $check = $this->gateService->canTransitionToExecution($project);

            if (!$check['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $check['reason'],
                ], 422);
            }
        }

        $project->update(['status' => $newStatus]);

        $this->auditService->logStatusChanged('project', $project->id, $oldStatus, $newStatus);

        return new ProjectResource($project->load('creator'));
    }

    public function archive(Project $project)
    {
        $this->authorize('archive', $project);

        $before = $project->toArray();
        $project->delete(); // soft delete

        $this->auditService->logUpdated('project', $project, $before, ['deleted_at' => now()->toISOString()]);

        return response()->json(['success' => true, 'message' => 'Project archived.']);
    }
}
