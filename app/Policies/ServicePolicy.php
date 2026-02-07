<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    /**
     * Create Service.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Update Service.
     */
    public function update(User $user, Service $service): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Delete service (soft delete).
     */
    public function delete(User $user, Service $service): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Toggle service active/inactive.
     */
    public function toggleActive(User $user, Service $service): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Reorder services.
     */
    public function reorder(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}
