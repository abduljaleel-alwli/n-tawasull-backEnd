<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteUser
{
    public function execute(User $user): void
    {
        abort_if(
            auth()->id() === $user->id,
            403,
            'You cannot delete your own account'
        );

        $user->delete();
    }
}

