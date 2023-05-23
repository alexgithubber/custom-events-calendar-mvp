<?php

namespace App\Services;

use App\DTOs\UserDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function create(UserDTO $userDTO): UserDTO
    {
        $userModel = User::create([
            'name' => $userDTO->name,
            'email' => $userDTO->email,
            'password' => Hash::make($userDTO->password),
        ]);

        $userData = $userModel->toArray();
        $userData['token'] = $userModel->createToken('auth_token')->plainTextToken;

        return UserDTO::fromArray($userData);
    }
}
