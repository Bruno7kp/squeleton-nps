<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);
if (file_exists($root . '/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

$dbPath = $_ENV['DB_PATH'] ?? 'database/app.sqlite';
$fullPath = $root . '/' . ltrim($dbPath, '/');
$dbDir = dirname($fullPath);

if (!is_dir($dbDir)) {
    mkdir($dbDir, 0775, true);
}

$pdo = new PDO('sqlite:' . $fullPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  filename TEXT NOT NULL UNIQUE,
  executed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
)');

$migrationFiles = glob($root . '/database/migrations/*.sql') ?: [];
sort($migrationFiles);

$checkStmt = $pdo->prepare('SELECT COUNT(1) FROM schema_migrations WHERE filename = :filename');
$insertStmt = $pdo->prepare('INSERT INTO schema_migrations (filename) VALUES (:filename)');

foreach ($migrationFiles as $file) {
    $filename = basename($file);

    $checkStmt->execute(['filename' => $filename]);
    $alreadyExecuted = (int) $checkStmt->fetchColumn() > 0;

    if ($alreadyExecuted) {
        echo "- Pulando migracao ja executada: {$filename}" . PHP_EOL;
        continue;
    }

    $sql = file_get_contents($file);
    if ($sql === false) {
        throw new RuntimeException("Falha ao ler migracao: {$filename}");
    }

    $pdo->beginTransaction();

    try {
        $pdo->exec($sql);
        $insertStmt->execute(['filename' => $filename]);
        $pdo->commit();
        echo "+ Migracao aplicada: {$filename}" . PHP_EOL;
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

echo 'Migracoes finalizadas.' . PHP_EOL;
