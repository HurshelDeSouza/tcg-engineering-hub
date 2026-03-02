<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArtifactResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'project_id'     => $this->project_id,
            'type'           => $this->type,
            'type_label'     => $this->type_label,
            'status'         => $this->status,
            'owner'          => new UserResource($this->whenLoaded('owner')),
            'owner_user_id'  => $this->owner_user_id,
            'content_json'   => $this->content_json,
            'completed_at'   => $this->completed_at?->toISOString(),
            'blocked_reason' => $this->blocked_reason ?? null,
            'created_at'     => $this->created_at?->toISOString(),
            'updated_at'     => $this->updated_at?->toISOString(),
        ];
    }
}
