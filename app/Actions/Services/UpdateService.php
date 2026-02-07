<?php

namespace App\Actions\Services;

use App\Models\Service;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Support\Auditable;

class UpdateService
{
    use Auditable;

    public function execute(Service $service, array $data): Service
    {
        Gate::authorize('update', $service);

        return DB::transaction(function () use ($service, $data) {
            /* =====================
               Main Image
            ===================== */
            // 🔴 Main image replacement
            if (array_key_exists('main_image', $data) && $data['main_image']) {

                // Delete old main image
                if ($service->main_image) {
                    Storage::disk('public')->delete($service->main_image);
                }

                // Store new main image
                $service->main_image = $data['main_image']->store('services', 'public');
            }

            /* =====================
               Gallery Images
            ===================== */
            // 🔴 Gallery replacement (only if new images sent)
            if (
                array_key_exists('images', $data)
                && is_array($data['images'])
                && count($data['images']) > 0
            ) {

                // Delete old gallery images
                if (is_array($service->images)) {
                    foreach ($service->images as $oldImage) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }

                // Store new gallery images
                $paths = [];
                foreach ($data['images'] as $image) {
                    $paths[] = $image->store('services/gallery', 'public');
                }

                $service->images = $paths;
            }

            /* =====================
               Update Fields
            ===================== */
            // 🔵 Update basic fields
            $service->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'is_active' => $data['is_active'] ?? $service->is_active,
                'display_order' => $data['display_order'] ?? $service->display_order,
                'tags' => !empty($data['tags']) ? json_encode($data['tags']) : null, // إضافة الـ tags هنا
            ]);

            /* =====================
            Update Tags
            ===================== */
            // if (array_key_exists('tags', $data) && !empty($data['tags'])) {
            //     $tags = is_string($data['tags']) ? array_map('trim', explode(',', $data['tags'])) : $data['tags'];
            //     $service->tags = !empty($tags) ? json_encode($tags) : null;
            // }


            /* =====================
               Audit
            ===================== */

            $this->audit('service.updated', $service, [
                'updated_fields' => array_keys($data),
            ]);

            return $service->refresh();
        });
    }
}
