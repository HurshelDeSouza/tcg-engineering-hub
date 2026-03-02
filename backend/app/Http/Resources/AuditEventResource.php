<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuditEventResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'actor'       => new UserResource($this->whenLoaded('actor')),
            'entity_type' => $this->entity_type,
            'entity_id'   => $this->entity_id,
            'action'      => $this->action,
            'before_json' => $this->before_json,
            'after_json'  => $this->after_json,
            'created_at'  => $this->created_at?->toISOString(),
        ];
    }
}
