<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * فقط super-admin يرى المستخدمين
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole('super-admin');
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasRole('super-admin');
    }

    /* =====================
       Update User
    ===================== */
    public function update(User $authUser, User $user): bool
    {
        if (! $authUser->hasRole('super-admin')) {
            return false;
        }

        // لا أحد يعدل نفسه
        return $authUser->id !== $user->id;
    }

    /* =====================
       Toggle Active
    ===================== */
    public function toggleActive(User $authUser, User $user): bool
    {
        if (! $authUser->hasRole('super-admin')) {
            return false;
        }

        // لا أحد يعطل نفسه
        return $authUser->id !== $user->id;
    }

    /* =====================
       Delete User
    ===================== */
    public function delete(User $authUser, User $user): bool
    {
        if (! $authUser->hasRole('super-admin')) {
            return false;
        }

        // لا أحد يحذف نفسه
        return $authUser->id !== $user->id;
    }

    /* =====================
       Change Role
    ===================== */
    public function changeRole(User $authUser, User $user): bool
    {
        if (! $authUser->hasRole('super-admin')) {
            return false;
        }

        // لا أحد يغير role نفسه
        return $authUser->id !== $user->id;
    }
}
