<?php
declare(strict_types=1);

namespace App;

use Exception;

class PasswordHasher
{
    public function hashPassword(string $password): string
    {
        $result = password_hash($password, PASSWORD_DEFAULT);
        if (!$result) {
            throw new Exception("password_hash failed");
        }
        return $result;
    }


    public function verifyPassword(string $password, string $hashedPassword)
    {
        return password_verify($password, $hashedPassword);
    }
}
