<?php

declare(strict_types=1);

namespace App\Support;

final class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    public static function token(): string
    {
        $token = (string) ($_SESSION[self::SESSION_KEY] ?? '');
        if ($token !== '') {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION[self::SESSION_KEY] = $token;

        return $token;
    }

    public static function hiddenInput(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_csrf" value="' . $token . '">';
    }

    public static function isValid(string $token): bool
    {
        $expected = (string) ($_SESSION[self::SESSION_KEY] ?? '');
        if ($expected === '' || $token === '') {
            return false;
        }

        return hash_equals($expected, $token);
    }
}
