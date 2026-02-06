<?php

namespace App\Actions\Projects;

use App\Models\Project;
use App\Support\Auditable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DeleteProject
{
    use Auditable;

    public function execute(Project $project): void
    {
        Gate::authorize('delete', $project);

        DB::transaction(function () use ($project) {
            /* =====================
               Delete Images
            ===================== */

            if ($project->main_image) {
                Storage::disk('public')->delete($project->main_image);
            }

            if (is_array($project->images)) {
                foreach ($project->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            /* =====================
               Soft Delete Project
            ===================== */

            $project->delete();

            /* =====================
               Audit
            ===================== */

            $this->audit(
                'project.deleted',
                $project,
                [
                    'title' => $project->title,
                ]
            );
        });
    }
}
