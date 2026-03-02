<?php

namespace App\Policies;

use App\Models\Artifact;
use App\Models\User;

class ArtifactPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Artifact $artifact): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'pm']);
    }

    public function update(User $user, Artifact $artifact): bool
    {
        return $user->hasRole(['admin', 'pm']);
    }

    public function delete(User $user, Artifact $artifact): bool
    {
        return $user->hasRole(['admin', 'pm']);
    }

    public function complete(User $user, Artifact $artifact): bool
    {
        return $user->hasRole(['admin', 'pm']);
    }
}
