<?php

namespace App\Actions\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Support\Auditable;

class UpdateProject
{
    use Auditable;

    public function execute(Project $project, array $data): Project
    {
        Gate::authorize('update', $project);

        return DB::transaction(function () use ($project, $data) {

            /* =====================
               Main Image
            ===================== */
            if (!empty($data['main_image'])) {
                if ($project->main_image) {
                    Storage::disk('public')->delete($project->main_image);
                }

                $project->main_image = $data['main_image']->store('projects', 'public');
            }

            /* =====================
               Gallery Images (replace only if new provided)
            ===================== */
            if (!empty($data['images']) && is_array($data['images']) && count($data['images']) > 0) {

                // delete old gallery
                if (is_array($project->images)) {
                    foreach ($project->images as $oldImage) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }

                $paths = [];
                foreach ($data['images'] as $image) {
                    $paths[] = $image->store('projects/gallery', 'public');
                }

                $project->images = $paths;
            }

            /* =====================
               Fill other fields
            ===================== */
            // ✅ اعتمد على casts: features/ images arrays تلقائيًا
            $project->fill([
                'title'         => $data['title'] ?? $project->title,
                'description'   => $data['description'] ?? null,
                'category_id'   => $data['category_id'] ?? null,
                'is_active'     => $data['is_active'] ?? $project->is_active,
                'display_order' => $data['display_order'] ?? $project->display_order,

                // ✅ features array (أو null)
                'features'      => array_key_exists('features', $data) ? ($data['features'] ?? null) : $project->features,

                // ✅ content html string (أو null)
                'content'       => array_key_exists('content', $data) ? ($data['content'] ?? null) : $project->content,
            ]);

            $project->save();

            /* =====================
               Audit
            ===================== */
            $this->audit('project.updated', $project, [
                'updated_fields' => array_keys($data),
            ]);

            return $project->refresh();
        });
    }
}
