<?php

namespace App\Actions\Services;

use App\Models\Service;
use App\Support\Auditable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DeleteService
{
    use Auditable;

    public function execute(Service $service): void
    {
        Gate::authorize('delete', $service);

        DB::transaction(function () use ($service) {

            /* =====================
               Delete Images
            ===================== */

            if ($service->main_image) {
                Storage::disk('public')->delete($service->main_image);
            }

            if (is_array($service->images)) {
                foreach ($service->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            /* =====================
               Soft Delete Service
            ===================== */

            $service->delete();

            /* =====================
               Audit
            ===================== */

            $this->audit(
                'service.deleted',
                $service,
                [
                    'title' => $service->title,
                ]
            );
        });
    }
}
