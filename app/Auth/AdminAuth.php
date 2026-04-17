<?php

declare(strict_types=1);

namespace App\Auth;

final class AdminAuth
{
    private const SESSION_KEY = 'admin_auth';

    public static function attempt(string $username, string $password): bool
    {
        $expectedUser = (string) ($_ENV['ADMIN_USER'] ?? 'admin');
        $expectedPass = (string) ($_ENV['ADMIN_PASS'] ?? 'admin123');

        return hash_equals($expectedUser, $username) && hash_equals($expectedPass, $password);
    }

    public static function login(string $username): void
    {
        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = [
            'username' => $username,
            'authenticated_at' => gmdate('c'),
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        session_regenerate_id(true);
    }

    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]);
    }

    public static function user(): ?array
    {
        $user = $_SESSION[self::SESSION_KEY] ?? null;

        return is_array($user) ? $user : null;
    }
}
