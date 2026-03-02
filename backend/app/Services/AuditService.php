<?php

namespace App\Services;

use App\Models\AuditEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public function log(
        string $entityType,
        int    $entityId,
        string $action,
        ?array $before = null,
        ?array $after  = null,
        ?int   $actorId = null
    ): AuditEvent {
        return AuditEvent::create([
            'actor_user_id' => $actorId ?? Auth::id(),
            'entity_type'   => $entityType,
            'entity_id'     => $entityId,
            'action'        => $action,
            'before_json'   => $before,
            'after_json'    => $after,
            'created_at'    => now(),
        ]);
    }

    public function logCreated(string $entityType, Model $model): AuditEvent
    {
        return $this->log($entityType, $model->id, 'created', null, $model->toArray());
    }

    public function logUpdated(string $entityType, Model $model, array $before, array $after): AuditEvent
    {
        // Only log fields that actually changed
        $changedBefore = [];
        $changedAfter  = [];

        foreach ($after as $key => $value) {
            if (($before[$key] ?? null) !== $value) {
                $changedBefore[$key] = $before[$key] ?? null;
                $changedAfter[$key]  = $value;
            }
        }

        return $this->log($entityType, $model->id, 'updated', $changedBefore, $changedAfter);
    }

    public function logStatusChanged(string $entityType, int $entityId, string $from, string $to): AuditEvent
    {
        return $this->log($entityType, $entityId, 'status_changed', ['status' => $from], ['status' => $to]);
    }

    public function logValidated(string $entityType, int $entityId): AuditEvent
    {
        return $this->log($entityType, $entityId, 'validated');
    }

    public function logCompleted(string $entityType, int $entityId): AuditEvent
    {
        return $this->log($entityType, $entityId, 'completed');
    }

    /**
     * Get audit timeline for a project (includes project, artifact, module events)
     */
    public function getProjectTimeline(int $projectId, int $perPage = 20)
    {
        // Get module and artifact IDs for this project
        $artifactIds = \App\Models\Artifact::where('project_id', $projectId)->pluck('id')->toArray();
        $moduleIds   = \App\Models\Module::where('project_id', $projectId)->pluck('id')->toArray();

        return AuditEvent::with('actor')
            ->where(function ($query) use ($projectId, $artifactIds, $moduleIds) {
                $query->where(fn($q) => $q->where('entity_type', 'project')->where('entity_id', $projectId));
                
                if (!empty($artifactIds)) {
                    $query->orWhere(fn($q) => $q->where('entity_type', 'artifact')->whereIn('entity_id', $artifactIds));
                }
                
                if (!empty($moduleIds)) {
                    $query->orWhere(fn($q) => $q->where('entity_type', 'module')->whereIn('entity_id', $moduleIds));
                }
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
