<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUser
{
    public function execute(array $data): User
    {
        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            // Temporary random password (user will reset it)
            'password'   => Hash::make(Str::random(32)),
            'note'       => $data['note'] ?? null,
            'created_by' => auth()->id(),
            'is_active'  => true,
        ]);

        $user->assignRole($data['role']);

        return $user;
    }
}
