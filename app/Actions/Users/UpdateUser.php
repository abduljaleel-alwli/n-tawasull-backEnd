<?php

namespace App\Actions\Users;

use App\Models\User;

class UpdateUser
{
    public function execute(User $user, array $data): User
    {
        // تحديث بيانات المستخدم فقط
        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
            'note'  => $data['note'] ?? null,
        ]);

        // تحديث الدور (Spatie way)
        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user;
    }
}


