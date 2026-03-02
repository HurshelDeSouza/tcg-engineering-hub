<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'project_id', 'domain', 'name', 'status',
        'objective', 'inputs', 'data_structure', 'logic_rules', 'outputs',
        'responsibility', 'failure_scenarios', 'audit_trail_requirements',
        'dependencies', 'version_note',
    ];

    protected $casts = [
        'inputs'       => 'array',
        'outputs'      => 'array',
        'dependencies' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Check if this module meets all validation requirements
     * Returns array: ['valid' => bool, 'errors' => string[]]
     */
    public function validationCheck(): array
    {
        $errors = [];

        if (empty($this->objective)) {
            $errors[] = 'The "objective" field is required.';
        }

        if (empty($this->inputs) || count($this->inputs) < 1) {
            $errors[] = 'Must have at least 1 "input".';
        }

        if (empty($this->outputs) || count($this->outputs) < 1) {
            $errors[] = 'Must have at least 1 "output".';
        }

        if (empty($this->responsibility)) {
            $errors[] = 'The "responsibility" field is required.';
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }
}
