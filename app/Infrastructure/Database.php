<?php

declare(strict_types=1);

namespace App\Infrastructure;

use PDO;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $dbPath = $_ENV['DB_PATH'] ?? 'database/app.sqlite';
        $absolutePath = dirname(__DIR__, 2) . '/' . ltrim($dbPath, '/');
        $directory = dirname($absolutePath);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Nao foi possivel criar o diretorio do banco SQLite.');
        }

        $pdo = new PDO('sqlite:' . $absolutePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');

        self::$connection = $pdo;

        return self::$connection;
    }
}
