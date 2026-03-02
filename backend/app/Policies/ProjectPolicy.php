<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // all roles can list projects
    }

    public function view(User $user, Project $project): bool
    {
        return true; // all roles can view
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'pm']);
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasRole(['admin', 'pm']);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasRole(['admin', 'pm']);
    }

    public function archive(User $user, Project $project): bool
    {
        return $user->hasRole(['admin', 'pm']);
    }
}
