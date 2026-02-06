<?php

namespace App\Actions\Projects;

use App\Models\Project;
use App\Support\Auditable;
use Illuminate\Support\Facades\Gate;

class ToggleProjectStatus
{
    use Auditable;

    public function execute(Project $project): Project
    {
        Gate::authorize('update', $project);
        
        $project->update([
            'is_active' => ! $project->is_active,
        ]);

        $this->audit('project.toggled', $project, [
            'is_active' => $project->is_active,
        ]);

        return $project;
    }
}
