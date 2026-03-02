<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                       => $this->id,
            'project_id'               => $this->project_id,
            'domain'                   => $this->domain,
            'name'                     => $this->name,
            'status'                   => $this->status,
            'objective'                => $this->objective,
            'inputs'                   => $this->inputs ?? [],
            'data_structure'           => $this->data_structure,
            'logic_rules'              => $this->logic_rules,
            'outputs'                  => $this->outputs ?? [],
            'responsibility'           => $this->responsibility,
            'failure_scenarios'        => $this->failure_scenarios,
            'audit_trail_requirements' => $this->audit_trail_requirements,
            'dependencies'             => $this->dependencies ?? [],
            'version_note'             => $this->version_note,
            'validation_check'         => $this->validation_check ?? $this->validationCheck(),
            'created_at'               => $this->created_at?->toISOString(),
            'updated_at'               => $this->updated_at?->toISOString(),
        ];
    }
}
