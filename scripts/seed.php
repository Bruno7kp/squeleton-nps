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

$pdo = new PDO('sqlite:' . $fullPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec('CREATE TABLE IF NOT EXISTS app_meta (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  meta_key TEXT NOT NULL UNIQUE,
  meta_value TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
)');

$stmt = $pdo->prepare('INSERT OR IGNORE INTO app_meta (meta_key, meta_value) VALUES (:key, :value)');
$stmt->execute([
    'key' => 'seed_version',
    'value' => 'phase-0',
]);

echo 'Seed inicial aplicado.' . PHP_EOL;
