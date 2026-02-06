<?php

namespace App\Actions\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Support\Auditable;

class CreateProject
{
    use Auditable;

    /**
     * Create a new project.
     */
    public function execute(array $data): Project
    {
        Gate::authorize('create', Project::class);

        return DB::transaction(function () use ($data) {

            /* =====================
               Images Handling
            ===================== */

            $mainImagePath = null;
            if (!empty($data['main_image'])) {
                $mainImagePath = $data['main_image']->store('projects', 'public');
            }

            $images = [];
            if (!empty($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $image) {
                    $images[] = $image->store('projects/gallery', 'public');
                }
            }

            /* =====================
               Create Project
            ===================== */

            $nextOrder = (Project::max('display_order') ?? 0) + 1;

            $project = Project::create([
                'title'         => $data['title'],
                'description'   => $data['description'] ?? null,
                'category_id'   => $data['category_id'] ?? null,
                'main_image'    => $mainImagePath,

                // ✅ JSON casts تتكفل بالتحويل
                'images'        => $images ?: null,
                'features'      => $data['features'] ?? null,

                // ✅ HTML نص عادي
                'content'       => $data['content'] ?? null,

                'is_active'     => $data['is_active'] ?? true,
                'display_order' => $nextOrder,
                'created_by'    => Auth::id(),
            ]);

            /* =====================
               Audit
            ===================== */

            $this->audit('project.created', $project, [
                'title' => $project->title,
            ]);

            return $project;
        });
    }
}
