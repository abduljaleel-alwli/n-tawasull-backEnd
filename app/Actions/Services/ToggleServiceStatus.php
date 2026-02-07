<?php

namespace App\Actions\Services;

use App\Models\Service;
use App\Support\Auditable;
use Illuminate\Support\Facades\Gate;

class ToggleServiceStatus
{
    use Auditable;

    public function execute(Service $service): Service
    {
        Gate::authorize('update', $service);

        $service->update([
            'is_active' => ! $service->is_active,
        ]);

        $this->audit('service.toggled', $service, [
            'is_active' => $service->is_active,
        ]);

        return $service;
    }
}
