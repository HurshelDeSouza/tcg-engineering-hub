<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'client_name', 'status', 'created_by'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function artifacts()
    {
        return $this->hasMany(Artifact::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function auditEvents()
    {
        return $this->morphMany(AuditEvent::class, 'entity')
            ->orWhere(function ($q) {
                // Also return artifact and module audit events for this project
            });
    }

    public function getArtifactByType(string $type): ?Artifact
    {
        return $this->artifacts()->where('type', $type)->first();
    }
}
