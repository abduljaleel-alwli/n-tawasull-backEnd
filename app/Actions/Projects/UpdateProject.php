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
               Videos Handling (JSON)
               Update only if provided in $data
            ===================== */
            $videos = $project->videos;

            if (array_key_exists('videos', $data)) {
                $videos = null;

                if (!empty($data['videos']) && is_array($data['videos'])) {
                    $normalized = [];

                    foreach ($data['videos'] as $item) {
                        if (!is_array($item)) continue;

                        $type = ($item['type'] ?? 'url');
                        $type = in_array($type, ['url', 'iframe'], true) ? $type : 'url';

                        $title = isset($item['title']) && is_string($item['title'])
                            ? trim($item['title'])
                            : null;

                        $provider = isset($item['provider']) && is_string($item['provider'])
                            ? strtolower(trim($item['provider']))
                            : 'other';

                        $provider = in_array($provider, ['youtube', 'vimeo', 'other'], true)
                            ? $provider
                            : 'other';

                        $url = isset($item['url']) && is_string($item['url']) ? trim($item['url']) : null;
                        $iframe = isset($item['iframe']) && is_string($item['iframe']) ? trim($item['iframe']) : null;

                        if ($type === 'url') {
                            if (!$url) continue;
                            if (!filter_var($url, FILTER_VALIDATE_URL)) continue;

                            $normalized[] = [
                                'type'     => 'url',
                                'provider' => $provider,
                                'title'    => $title,
                                'url'      => $url,
                                'iframe'   => null,
                            ];
                        } else {
                            if (!$iframe) continue;

                            // إزالة <script> إن وجدت (حماية أولية)
                            $iframe = preg_replace('/<\s*script[^>]*>.*?<\s*\/\s*script\s*>/is', '', $iframe);

                            $normalized[] = [
                                'type'     => 'iframe',
                                'provider' => $provider,
                                'title'    => $title,
                                'url'      => null,
                                'iframe'   => $iframe,
                            ];
                        }
                    }

                    $videos = !empty($normalized) ? array_values($normalized) : null;
                }
            }

            /* =====================
               Fill other fields
            ===================== */
            // ✅ اعتمد على casts: features/ images/videos arrays تلقائيًا
            $project->fill([
                'title'         => $data['title'] ?? $project->title,
                'description'   => $data['description'] ?? null,
                'category_id'   => $data['category_id'] ?? null,
                'is_active'     => $data['is_active'] ?? $project->is_active,
                'display_order' => $data['display_order'] ?? $project->display_order,

                // ✅ features array (أو null)
                'features'      => array_key_exists('features', $data)
                    ? ($data['features'] ?? null)
                    : $project->features,

                // ✅ content html string (أو null)
                'content'       => array_key_exists('content', $data)
                    ? ($data['content'] ?? null)
                    : $project->content,

                // ✅ videos json (أو null) — فقط لو تم تمريره
                'videos'        => $videos,
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
