<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artifact extends Model
{
    protected $fillable = [
        'project_id', 'type', 'status', 'owner_user_id', 'content_json', 'completed_at',
    ];

    protected $casts = [
        'content_json' => 'array',
        'completed_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    // Human-readable label
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'strategic_alignment'  => 'Strategic Alignment',
            'big_picture'          => 'Big Picture',
            'domain_breakdown'     => 'Domain Breakdown',
            'module_matrix'        => 'Module Matrix',
            'module_engineering'   => 'Module Engineering',
            'system_architecture'  => 'System Architecture',
            'phase_scope'          => 'Phase Scope',
            default                => ucfirst($this->type),
        };
    }
}
