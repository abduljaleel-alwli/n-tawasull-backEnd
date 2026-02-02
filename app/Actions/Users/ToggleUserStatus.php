<?php

namespace App\Actions\Users;

use App\Models\User;

class ToggleUserStatus
{
    public function execute(User $user): void
    {
        abort_if(
            auth()->id() === $user->id,
            403,
            'You cannot change your own account status'
        );

        $user->update([
            'is_active' => ! $user->is_active,
        ]);
    }
}


