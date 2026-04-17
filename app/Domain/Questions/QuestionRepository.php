<?php

declare(strict_types=1);

namespace App\Domain\Questions;

use App\Infrastructure\Database;
use PDO;

final class QuestionRepository
{
    public function listBySurvey(int $surveyId): array
    {
        $stmt = $this->connection()->prepare(
            'SELECT id, survey_id, label, field_name, question_type, position, is_required,
                    placeholder, help_text, options_json, scale_min, scale_max
             FROM questions
             WHERE survey_id = :survey_id
             ORDER BY position ASC, id ASC'
        );
        $stmt->execute(['survey_id' => $surveyId]);

        $rows = $stmt->fetchAll() ?: [];

        foreach ($rows as &$row) {
            $row['options'] = $this->decodeOptions($row['options_json'] ?? null);
        }

        return $rows;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->connection()->prepare(
            'SELECT id, survey_id, label, field_name, question_type, position, is_required,
                    placeholder, help_text, options_json, scale_min, scale_max
             FROM questions
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $question = $stmt->fetch();
        if (!is_array($question)) {
            return null;
        }

        $question['options'] = $this->decodeOptions($question['options_json'] ?? null);
        return $question;
    }

    public function create(array $data): int
    {
        $position = $this->nextPosition((int) $data['survey_id']);

        $stmt = $this->connection()->prepare(
            'INSERT INTO questions (
                survey_id, label, field_name, question_type, position, is_required,
                placeholder, help_text, options_json, scale_min, scale_max
             ) VALUES (
                :survey_id, :label, :field_name, :question_type, :position, :is_required,
                :placeholder, :help_text, :options_json, :scale_min, :scale_max
             )'
        );

        $stmt->execute([
            'survey_id' => $data['survey_id'],
            'label' => $data['label'],
            'field_name' => $data['field_name'],
            'question_type' => $data['question_type'],
            'position' => $position,
            'is_required' => $data['is_required'],
            'placeholder' => $data['placeholder'],
            'help_text' => $data['help_text'],
            'options_json' => $data['options_json'],
            'scale_min' => $data['scale_min'],
            'scale_max' => $data['scale_max'],
        ]);

        return (int) $this->connection()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->connection()->prepare(
            'UPDATE questions
             SET label = :label,
                 field_name = :field_name,
                 question_type = :question_type,
                 is_required = :is_required,
                 placeholder = :placeholder,
                 help_text = :help_text,
                 options_json = :options_json,
                 scale_min = :scale_min,
                 scale_max = :scale_max,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'label' => $data['label'],
            'field_name' => $data['field_name'],
            'question_type' => $data['question_type'],
            'is_required' => $data['is_required'],
            'placeholder' => $data['placeholder'],
            'help_text' => $data['help_text'],
            'options_json' => $data['options_json'],
            'scale_min' => $data['scale_min'],
            'scale_max' => $data['scale_max'],
        ]);
    }

    public function delete(int $id): void
    {
        $question = $this->findById($id);
        if ($question === null) {
            return;
        }

        $stmt = $this->connection()->prepare('DELETE FROM questions WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $this->normalizePositions((int) $question['survey_id']);
    }

    public function fieldNameExists(int $surveyId, string $fieldName, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(1) FROM questions WHERE survey_id = :survey_id AND field_name = :field_name';
        $params = [
            'survey_id' => $surveyId,
            'field_name' => $fieldName,
        ];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $params['except_id'] = $exceptId;
        }

        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function move(int $id, string $direction): void
    {
        $question = $this->findById($id);
        if ($question === null) {
            return;
        }

        $surveyId = (int) $question['survey_id'];
        $position = (int) $question['position'];
        $targetPosition = $direction === 'up' ? $position - 1 : $position + 1;

        if ($targetPosition < 1) {
            return;
        }

        $swapStmt = $this->connection()->prepare(
            'SELECT id FROM questions WHERE survey_id = :survey_id AND position = :position LIMIT 1'
        );
        $swapStmt->execute([
            'survey_id' => $surveyId,
            'position' => $targetPosition,
        ]);

        $swapId = $swapStmt->fetchColumn();
        if ($swapId === false) {
            return;
        }

        $this->connection()->beginTransaction();
        try {
            $stmt = $this->connection()->prepare('UPDATE questions SET position = :position, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
            $stmt->execute(['position' => $targetPosition, 'id' => $id]);
            $stmt->execute(['position' => $position, 'id' => (int) $swapId]);
            $this->connection()->commit();
        } catch (\Throwable $exception) {
            if ($this->connection()->inTransaction()) {
                $this->connection()->rollBack();
            }

            throw $exception;
        }
    }

    public function normalizePositions(int $surveyId): void
    {
        $idsStmt = $this->connection()->prepare(
            'SELECT id FROM questions WHERE survey_id = :survey_id ORDER BY position ASC, id ASC'
        );
        $idsStmt->execute(['survey_id' => $surveyId]);
        $ids = $idsStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $stmt = $this->connection()->prepare('UPDATE questions SET position = :position, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $position = 1;
        foreach ($ids as $id) {
            $stmt->execute([
                'position' => $position,
                'id' => (int) $id,
            ]);
            $position++;
        }
    }

    private function nextPosition(int $surveyId): int
    {
        $stmt = $this->connection()->prepare('SELECT COALESCE(MAX(position), 0) + 1 FROM questions WHERE survey_id = :survey_id');
        $stmt->execute(['survey_id' => $surveyId]);

        return (int) $stmt->fetchColumn();
    }

    private function decodeOptions(?string $optionsJson): array
    {
        if ($optionsJson === null || $optionsJson === '') {
            return [];
        }

        $decoded = json_decode($optionsJson, true);
        if (!is_array($decoded)) {
            return [];
        }

        $clean = [];
        foreach ($decoded as $item) {
            $value = trim((string) $item);
            if ($value !== '') {
                $clean[] = $value;
            }
        }

        return $clean;
    }

    private function connection(): PDO
    {
        return Database::connection();
    }
}
