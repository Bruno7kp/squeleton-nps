<?php

declare(strict_types=1);

namespace App\Domain\Questions;

use App\Infrastructure\Database;
use PDO;

final class SurveyRuleRepository
{
    public function listBySurvey(int $surveyId): array
    {
        $stmt = $this->connection()->prepare(
            'SELECT r.id, r.survey_id, r.source_question_id, r.operator, r.compare_value,
                    r.target_question_id, r.action, r.position,
                    sq.label AS source_label, tq.label AS target_label
             FROM survey_rules r
             INNER JOIN questions sq ON sq.id = r.source_question_id
             INNER JOIN questions tq ON tq.id = r.target_question_id
             WHERE r.survey_id = :survey_id
             ORDER BY r.position ASC, r.id ASC'
        );
        $stmt->execute(['survey_id' => $surveyId]);

        return $stmt->fetchAll() ?: [];
    }

    public function create(array $data): int
    {
        $position = $this->nextPosition((int) $data['survey_id']);

        $stmt = $this->connection()->prepare(
            'INSERT INTO survey_rules (
                survey_id, source_question_id, operator, compare_value,
                target_question_id, action, position
             ) VALUES (
                :survey_id, :source_question_id, :operator, :compare_value,
                :target_question_id, :action, :position
             )'
        );

        $stmt->execute([
            'survey_id' => $data['survey_id'],
            'source_question_id' => $data['source_question_id'],
            'operator' => $data['operator'],
            'compare_value' => $data['compare_value'],
            'target_question_id' => $data['target_question_id'],
            'action' => $data['action'],
            'position' => $position,
        ]);

        return (int) $this->connection()->lastInsertId();
    }

    public function delete(int $id): void
    {
        $stmtFind = $this->connection()->prepare('SELECT survey_id FROM survey_rules WHERE id = :id LIMIT 1');
        $stmtFind->execute(['id' => $id]);
        $surveyId = $stmtFind->fetchColumn();
        if ($surveyId === false) {
            return;
        }

        $stmt = $this->connection()->prepare('DELETE FROM survey_rules WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $this->normalizePositions((int) $surveyId);
    }

    private function nextPosition(int $surveyId): int
    {
        $stmt = $this->connection()->prepare('SELECT COALESCE(MAX(position), 0) + 1 FROM survey_rules WHERE survey_id = :survey_id');
        $stmt->execute(['survey_id' => $surveyId]);

        return (int) $stmt->fetchColumn();
    }

    private function normalizePositions(int $surveyId): void
    {
        $idsStmt = $this->connection()->prepare(
            'SELECT id FROM survey_rules WHERE survey_id = :survey_id ORDER BY position ASC, id ASC'
        );
        $idsStmt->execute(['survey_id' => $surveyId]);
        $ids = $idsStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $stmt = $this->connection()->prepare('UPDATE survey_rules SET position = :position WHERE id = :id');
        $position = 1;
        foreach ($ids as $id) {
            $stmt->execute([
                'position' => $position,
                'id' => (int) $id,
            ]);
            $position++;
        }
    }

    private function connection(): PDO
    {
        return Database::connection();
    }
}
