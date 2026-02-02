<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChangeUserRole
{
    public function execute(User $user, string $role): User
    {
        DB::transaction(function () use ($user, $role) {
            // Remove existing roles (single role system)
            $user->syncRoles([$role]);
        });

        return $user;
    }
}
