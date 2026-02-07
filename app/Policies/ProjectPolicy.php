<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Create project.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Update project.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Delete project (soft delete).
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Toggle project active/inactive.
     */
    public function toggleActive(User $user, Project $project): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Reorder projects.
     */
    public function reorder(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}
