<?php

declare(strict_types=1);

namespace App\Domain\Surveys;

use App\Infrastructure\Database;
use PDO;

final class SurveyRepository
{
    public static function isValidSlug(string $slug): bool
    {
        return (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug);
    }

    public function listWithProject(): array
    {
        return $this->listFiltered(0);
    }

    public function listByProject(int $projectId): array
    {
        return $this->listFiltered($projectId);
    }

    private function listFiltered(int $projectId): array
    {
        if ($projectId > 0) {
            $stmt = $this->connection()->prepare(
                'SELECT s.id, s.project_id, s.name, s.slug, s.status, s.title, s.description,
                        s.created_at, s.updated_at, p.name AS project_name,
                        GROUP_CONCAT(st.trigger_key, ", ") AS trigger_keys
                 FROM surveys s
                 INNER JOIN projects p ON p.id = s.project_id
                 LEFT JOIN survey_triggers st ON st.survey_id = s.id
                 WHERE s.project_id = :project_id
                 GROUP BY s.id
                 ORDER BY s.id DESC'
            );
            $stmt->execute(['project_id' => $projectId]);
        } else {
            $stmt = $this->connection()->query(
                'SELECT s.id, s.project_id, s.name, s.slug, s.status, s.title, s.description,
                        s.created_at, s.updated_at, p.name AS project_name,
                        GROUP_CONCAT(st.trigger_key, ", ") AS trigger_keys
                 FROM surveys s
                 INNER JOIN projects p ON p.id = s.project_id
                 LEFT JOIN survey_triggers st ON st.survey_id = s.id
                 GROUP BY s.id
                 ORDER BY s.id DESC'
            );
        }

        $surveys = $stmt->fetchAll() ?: [];

        foreach ($surveys as &$survey) {
            $survey['trigger_keys'] = (string) ($survey['trigger_keys'] ?? '');
            $survey['trigger_event'] = $survey['trigger_keys'];
        }

        return $surveys;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->connection()->prepare(
            'SELECT s.id, s.project_id, s.name, s.slug, s.status, s.title, s.description,
                    GROUP_CONCAT(st.trigger_key, ",") AS trigger_keys_csv
             FROM surveys s
             LEFT JOIN survey_triggers st ON st.survey_id = s.id
             WHERE s.id = :id
             GROUP BY s.id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $survey = $stmt->fetch();

        if (!is_array($survey)) {
            return null;
        }

        $triggerKeys = [];
        if (!empty($survey['trigger_keys_csv'])) {
            $triggerKeys = array_values(array_filter(array_map(
                static fn (string $trigger): string => trim($trigger),
                explode(',', (string) $survey['trigger_keys_csv'])
            ), static fn (string $trigger): bool => $trigger !== ''));
        }

        unset($survey['trigger_keys_csv']);
        $survey['trigger_keys'] = $triggerKeys;
        $survey['trigger_event'] = $triggerKeys[0] ?? '';

        return $survey;
    }

    public function create(array $data): int
    {
        $stmt = $this->connection()->prepare(
            'INSERT INTO surveys (project_id, name, slug, status, trigger_event, title, description)
             VALUES (:project_id, :name, :slug, :status, :legacy_trigger_event, :title, :description)'
        );

        $stmt->execute([
            'project_id' => $data['project_id'],
            'name' => $data['name'],
            'slug' => $data['slug'],
            'status' => $data['status'],
            'legacy_trigger_event' => $data['legacy_trigger_event'] ?? '',
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
                 trigger_event = :legacy_trigger_event,
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
            'legacy_trigger_event' => $data['legacy_trigger_event'] ?? '',
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
            'SELECT s.id, s.project_id, s.name, s.slug, s.status, st.trigger_key AS trigger_event, s.title, s.description,
                    p.public_key
             FROM surveys s
             INNER JOIN survey_triggers st ON st.survey_id = s.id
             INNER JOIN projects p ON p.id = s.project_id
             WHERE p.public_key = :public_key
               AND st.trigger_key = :trigger_event
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
            'SELECT s.id, s.project_id, s.name, s.slug, s.status,
                    (
                        SELECT st.trigger_key
                        FROM survey_triggers st
                        WHERE st.survey_id = s.id
                        ORDER BY st.id ASC
                        LIMIT 1
                    ) AS trigger_event,
                    s.title, s.description,
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
