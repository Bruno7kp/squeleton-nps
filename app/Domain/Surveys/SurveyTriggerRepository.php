<?php

declare(strict_types=1);

namespace App\Domain\Surveys;

use App\Infrastructure\Database;
use PDO;

final class SurveyTriggerRepository
{
    public function findByProjectKeyAndTrigger(string $publicKey, string $triggerKey): ?array
    {
        $stmt = $this->connection()->prepare(
            'SELECT st.id, st.project_id, st.survey_id, st.trigger_key
             FROM survey_triggers st
             INNER JOIN projects p ON p.id = st.project_id
             WHERE p.public_key = :public_key
               AND st.trigger_key = :trigger_key
             LIMIT 1'
        );

        $stmt->execute([
            'public_key' => $publicKey,
            'trigger_key' => $triggerKey,
        ]);

        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    public function listBySurveyId(int $surveyId): array
    {
        $stmt = $this->connection()->prepare(
            'SELECT trigger_key
             FROM survey_triggers
             WHERE survey_id = :survey_id
             ORDER BY trigger_key ASC'
        );

        $stmt->execute(['survey_id' => $surveyId]);

        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        return array_values(array_map(static fn (mixed $row): string => (string) $row, $rows));
    }

    public function findConflicts(int $projectId, array $triggerKeys, ?int $exceptSurveyId = null): array
    {
        if ($triggerKeys === []) {
            return [];
        }

        $placeholders = [];
        $params = ['project_id' => $projectId];

        foreach (array_values($triggerKeys) as $idx => $triggerKey) {
            $key = 'trigger_' . $idx;
            $placeholders[] = ':' . $key;
            $params[$key] = $triggerKey;
        }

        $sql = 'SELECT st.trigger_key, s.id AS survey_id, s.name AS survey_name
                FROM survey_triggers st
                INNER JOIN surveys s ON s.id = st.survey_id
                WHERE st.project_id = :project_id
                  AND st.trigger_key IN (' . implode(', ', $placeholders) . ')';

        if ($exceptSurveyId !== null) {
            $sql .= ' AND st.survey_id <> :except_survey_id';
            $params['except_survey_id'] = $exceptSurveyId;
        }

        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll() ?: [];
    }

    public function replaceBySurveyId(int $surveyId, int $projectId, array $triggerKeys): void
    {
        $pdo = $this->connection();
        $pdo->beginTransaction();

        try {
            $this->deleteBySurveyId($surveyId);

            if ($triggerKeys !== []) {
                $insertStmt = $pdo->prepare(
                    'INSERT INTO survey_triggers (project_id, survey_id, trigger_key)
                     VALUES (:project_id, :survey_id, :trigger_key)'
                );

                foreach ($triggerKeys as $triggerKey) {
                    $insertStmt->execute([
                        'project_id' => $projectId,
                        'survey_id' => $surveyId,
                        'trigger_key' => $triggerKey,
                    ]);
                }
            }

            $pdo->commit();
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function deleteBySurveyId(int $surveyId): void
    {
        $stmt = $this->connection()->prepare('DELETE FROM survey_triggers WHERE survey_id = :survey_id');
        $stmt->execute(['survey_id' => $surveyId]);
    }

    public function deleteByTriggerKey(int $projectId, string $triggerKey): void
    {
        $stmt = $this->connection()->prepare(
            'DELETE FROM survey_triggers WHERE project_id = :project_id AND trigger_key = :trigger_key'
        );

        $stmt->execute([
            'project_id' => $projectId,
            'trigger_key' => $triggerKey,
        ]);
    }

    private function connection(): PDO
    {
        return Database::connection();
    }
}
