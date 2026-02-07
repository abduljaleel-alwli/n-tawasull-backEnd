<?php

namespace App\Actions\Services;

use App\Models\Service;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Support\Auditable;

class CreateService
{
    use Auditable;

    /**
     * Create a new service.
     */
    public function execute(array $data): Service
    {
        Gate::authorize('create', Service::class);

        return DB::transaction(function () use ($data) {

            /* ===================== 
               Images Handling
            ===================== */

            $mainImagePath = null;
            if (!empty($data['main_image'])) {
                $mainImagePath = $data['main_image']->store('services', 'public');
            }

            $images = [];
            if (!empty($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $image) {
                    $images[] = $image->store('services/gallery', 'public');
                }
            }

            /* ===================== 
               Display Order
            ===================== */

            $nextOrder = (Service::max('display_order') ?? 0) + 1;

            /* ===================== 
               Create Service
            ===================== */

            $service = Service::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'main_image' => $mainImagePath,
                'images' => $images,
                'is_active' => $data['is_active'] ?? true,
                'display_order' => $nextOrder,
                'created_by' => Auth::id(),
                'tags' => !empty($data['tags']) ? json_encode($data['tags']) : null, // إضافة الـ tags هنا
            ]);

            /* ===================== 
               Audit
            ===================== */

            $this->audit('service.created', $service, [
                'title' => $service->title,
            ]);

            return $service;
        });
    }
}
