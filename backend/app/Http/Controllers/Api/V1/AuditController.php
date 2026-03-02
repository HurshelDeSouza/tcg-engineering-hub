<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditEventResource;
use App\Models\Project;
use App\Services\AuditService;

class AuditController extends Controller
{
    public function __construct(private readonly AuditService $auditService) {}

    public function timeline(Project $project)
    {
        $this->authorize('view', $project);

        $events = $this->auditService->getProjectTimeline($project->id);

        return AuditEventResource::collection($events);
    }
}
