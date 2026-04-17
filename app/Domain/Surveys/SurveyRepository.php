<?php

declare(strict_types=1);

namespace App\Domain\Surveys;

use App\Infrastructure\Database;
use PDO;

final class SurveyRepository
{
    public function listWithProject(): array
    {
        $stmt = $this->connection()->query(
            'SELECT s.id, s.project_id, s.name, s.slug, s.status, s.trigger_event, s.title, s.description,
                    s.created_at, s.updated_at, p.name AS project_name
             FROM surveys s
             INNER JOIN projects p ON p.id = s.project_id
             ORDER BY s.id DESC'
        );

        return $stmt->fetchAll() ?: [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->connection()->prepare(
            'SELECT id, project_id, name, slug, status, trigger_event, title, description
             FROM surveys
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $survey = $stmt->fetch();

        return is_array($survey) ? $survey : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->connection()->prepare(
            'INSERT INTO surveys (project_id, name, slug, status, trigger_event, title, description)
             VALUES (:project_id, :name, :slug, :status, :trigger_event, :title, :description)'
        );

        $stmt->execute([
            'project_id' => $data['project_id'],
            'name' => $data['name'],
            'slug' => $data['slug'],
            'status' => $data['status'],
            'trigger_event' => $data['trigger_event'],
            'title' => $data['title'],
            'description' => $data['description'],
        ]);

        return (int) $this->connection()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->connection()->prepare(
            'UPDATE surveys
             SET project_id = :project_id,
                 name = :name,
                 slug = :slug,
                 status = :status,
                 trigger_event = :trigger_event,
                 title = :title,
                 description = :description,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'project_id' => $data['project_id'],
            'name' => $data['name'],
            'slug' => $data['slug'],
            'status' => $data['status'],
            'trigger_event' => $data['trigger_event'],
            'title' => $data['title'],
            'description' => $data['description'],
        ]);
    }

    public function slugExists(int $projectId, string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(1) FROM surveys WHERE project_id = :project_id AND slug = :slug';
        $params = [
            'project_id' => $projectId,
            'slug' => $slug,
        ];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $params['except_id'] = $exceptId;
        }

        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function findPublishedByProjectKeyAndTrigger(string $publicKey, string $triggerEvent): ?array
    {
        $stmt = $this->connection()->prepare(
            'SELECT s.id, s.project_id, s.name, s.slug, s.status, s.trigger_event, s.title, s.description,
                    p.public_key
             FROM surveys s
             INNER JOIN projects p ON p.id = s.project_id
             WHERE p.public_key = :public_key
               AND s.trigger_event = :trigger_event
               AND s.status = :status
             ORDER BY s.updated_at DESC
             LIMIT 1'
        );

        $stmt->execute([
            'public_key' => $publicKey,
            'trigger_event' => $triggerEvent,
            'status' => 'published',
        ]);

        $survey = $stmt->fetch();

        return is_array($survey) ? $survey : null;
    }

    public function findLatestPublishedByProjectKey(string $publicKey): ?array
    {
        $stmt = $this->connection()->prepare(
            'SELECT s.id, s.project_id, s.name, s.slug, s.status, s.trigger_event, s.title, s.description,
                    p.public_key
             FROM surveys s
             INNER JOIN projects p ON p.id = s.project_id
             WHERE p.public_key = :public_key
               AND s.status = :status
             ORDER BY s.updated_at DESC, s.id DESC
             LIMIT 1'
        );

        $stmt->execute([
            'public_key' => $publicKey,
            'status' => 'published',
        ]);

        $survey = $stmt->fetch();

        return is_array($survey) ? $survey : null;
    }

    private function connection(): PDO
    {
        return Database::connection();
    }
}
