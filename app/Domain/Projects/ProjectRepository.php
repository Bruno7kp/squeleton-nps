<?php

declare(strict_types=1);

namespace App\Domain\Projects;

use App\Infrastructure\Database;
use PDO;

final class ProjectRepository
{
    public static function generatePublicKey(): string
    {
        return 'nps_pk_' . bin2hex(random_bytes(8));
    }

    public static function isValidSlug(string $slug): bool
    {
        return (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug);
    }

    public function listAll(): array
    {
        $stmt = $this->connection()->query(
            'SELECT id, name, slug, public_key, description, is_active, created_at, updated_at
             FROM projects
             ORDER BY id DESC'
        );

        return $stmt->fetchAll() ?: [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->connection()->prepare(
            'SELECT id, name, slug, public_key, description, is_active, created_at, updated_at
             FROM projects
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $project = $stmt->fetch();

        return is_array($project) ? $project : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->connection()->prepare(
            'INSERT INTO projects (name, slug, public_key, description, is_active)
             VALUES (:name, :slug, :public_key, :description, :is_active)'
        );

        $stmt->execute([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'public_key' => $data['public_key'],
            'description' => $data['description'],
            'is_active' => $data['is_active'],
        ]);

        return (int) $this->connection()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->connection()->prepare(
            'UPDATE projects
             SET name = :name,
                 slug = :slug,
                 description = :description,
                 is_active = :is_active,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'is_active' => $data['is_active'],
        ]);
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(1) FROM projects WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $params['except_id'] = $exceptId;
        }

        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function connection(): PDO
    {
        return Database::connection();
    }
}
