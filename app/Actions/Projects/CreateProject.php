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
               Videos Handling (JSON)
               Expected: array of items
               [{type:"url|iframe", provider:"youtube|vimeo|other", title:null, url:null, iframe:null}]
            ===================== */

            $videos = null;

            if (!empty($data['videos']) && is_array($data['videos'])) {
                $normalized = [];

                foreach ($data['videos'] as $item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    $type = ($item['type'] ?? 'url');
                    $type = in_array($type, ['url', 'iframe'], true) ? $type : 'url';

                    $title = isset($item['title']) && is_string($item['title'])
                        ? trim($item['title'])
                        : null;

                    $provider = isset($item['provider']) && is_string($item['provider'])
                        ? strtolower(trim($item['provider']))
                        : 'other';

                    $provider = in_array($provider, ['youtube', 'vimeo', 'other'], true) ? $provider : 'other';

                    $url = isset($item['url']) && is_string($item['url']) ? trim($item['url']) : null;
                    $iframe = isset($item['iframe']) && is_string($item['iframe']) ? trim($item['iframe']) : null;

                    // ✅ فلترة بسيطة: لا نخزن عنصر فاضي
                    if ($type === 'url') {
                        if (!$url) continue;

                        // (اختياري) تأكد أنه URL صالح
                        if (!filter_var($url, FILTER_VALIDATE_URL)) continue;

                        $normalized[] = [
                            'type'     => 'url',
                            'provider' => $provider,
                            'title'    => $title,
                            'url'      => $url,
                            'iframe'   => null,
                        ];
                    } else {
                        // ⚠️ iframe خام قد يكون خطر، نسمح به لكن ننظفه بشكل بسيط
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

                // ✅ Videos JSON
                'videos'        => $videos,

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
