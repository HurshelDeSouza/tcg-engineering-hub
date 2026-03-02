<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'client_name' => $this->client_name,
            'status'      => $this->status,
            'created_by'  => $this->whenLoaded('creator', fn() => new UserResource($this->creator)),
            'artifacts'   => ArtifactResource::collection($this->whenLoaded('artifacts')),
            'modules'     => ModuleResource::collection($this->whenLoaded('modules')),
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
            'deleted_at'  => $this->deleted_at?->toISOString(),
        ];
    }
}
