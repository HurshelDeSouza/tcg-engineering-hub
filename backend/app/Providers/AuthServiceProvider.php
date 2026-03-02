<?php

namespace App\Providers;

use App\Models\Artifact;
use App\Models\Module;
use App\Models\Project;
use App\Policies\ArtifactPolicy;
use App\Policies\ModulePolicy;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class  => ProjectPolicy::class,
        Artifact::class => ArtifactPolicy::class,
        Module::class   => ModulePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
