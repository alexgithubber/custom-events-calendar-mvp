<?php

namespace App\DTOs;

use App\DTOs\Contracts\DTOInterface;

final class UserDTO implements DTOInterface
{
    public readonly string $name;
    public readonly string $email;
    public readonly ?string $password;
    public readonly ?int $id;
    public readonly ?string $token;
    public readonly ?string $createdAt;

    /**
     * @param string $name
     * @param string $email
     * @param string|null $password
     * @param int|null $id
     * @param string|null $token
     * @param string|null $createdAt
     */
    public function __construct(
        string $name,
        string $email,
        string $password = null,
        int $id = null,
        string $token = null,
        string $createdAt = null,
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->id = $id;
        $this->token = $token;
        $this->createdAt = $createdAt;
    }

    /**
     * @param array $fields
     * @return UserDTO
     */
    public static function fromArray(array $fields): UserDTO
    {
        return new self(
            name: $fields['name'],
            email: $fields['email'],
            password: $fields['password'] ?? null,
            id: $fields['id'] ?? null,
            token: $fields['token'] ?? null,
            createdAt: $fields['created_at'] ?? null,
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'token' => $this->token,
            'created_at' => $this->createdAt,
        ];
    }

    /**
     * @return array
     */
    public function extract(): array
    {
        return array_filter($this->toArray());
    }
}
