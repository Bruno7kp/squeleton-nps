<?php

declare(strict_types=1);

namespace App\Domain\Surveys;

use App\Infrastructure\Database;
use PDO;

final class SubmissionHandler
{
    public function handle(array $survey, array $answers, array $meta): array
    {
        $schema = $this->loadSchema((int) $survey['id']);
        $visibility = $this->computeVisibility($schema['questions'], $schema['rules'], $answers);

        ['errors' => $errors, 'validated' => $validated, 'score_nps' => $scoreNps] =
            $this->validateAnswers($schema['questions'], $visibility, $answers);

        if (!empty($errors)) {
            return ['ok' => false, 'errors' => $errors];
        }

        $submissionId = $this->persist($survey, $meta, $validated, $scoreNps);

        return [
            'ok' => true,
            'submission_id' => $submissionId,
            'stored_answers' => count($validated),
        ];
    }

    public function loadSchema(int $surveyId): array
    {
        $pdo = Database::connection();

        $questionsStmt = $pdo->prepare(
            'SELECT id, field_name, label, question_type, position, is_required,
                    placeholder, help_text, options_json, scale_min, scale_max
             FROM questions
             WHERE survey_id = :survey_id
             ORDER BY position ASC, id ASC'
        );
        $questionsStmt->execute(['survey_id' => $surveyId]);
        $questions = $questionsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($questions as &$question) {
            $decoded = json_decode((string) ($question['options_json'] ?? ''), true);
            $question['id'] = (int) $question['id'];
            $question['position'] = (int) $question['position'];
            $question['is_required'] = (int) $question['is_required'];
            $question['options'] = is_array($decoded) ? array_values($decoded) : [];
            $question['scale_min'] = $question['scale_min'] !== null ? (int) $question['scale_min'] : null;
            $question['scale_max'] = $question['scale_max'] !== null ? (int) $question['scale_max'] : null;
            unset($question['options_json']);
        }

        $rulesStmt = $pdo->prepare(
            'SELECT id, source_question_id, operator, compare_value,
                    target_question_id, action, position
             FROM survey_rules
             WHERE survey_id = :survey_id
             ORDER BY position ASC, id ASC'
        );
        $rulesStmt->execute(['survey_id' => $surveyId]);
        $rules = $rulesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rules as &$rule) {
            $rule['id'] = (int) $rule['id'];
            $rule['source_question_id'] = (int) $rule['source_question_id'];
            $rule['target_question_id'] = (int) $rule['target_question_id'];
            $rule['position'] = (int) $rule['position'];
        }

        return ['questions' => $questions, 'rules' => $rules];
    }

    private function computeVisibility(array $questions, array $rules, array $answers): array
    {
        $questionById = [];
        foreach ($questions as $q) {
            $questionById[(int) $q['id']] = $q;
        }

        $rulesByTarget = [];
        foreach ($rules as $rule) {
            $rulesByTarget[(int) $rule['target_question_id']][] = $rule;
        }

        $visibility = [];
        foreach ($questions as $question) {
            $id = (int) $question['id'];

            if (!isset($rulesByTarget[$id])) {
                $visibility[$id] = true;
                continue;
            }

            $visible = false;
            foreach ($rulesByTarget[$id] as $rule) {
                $source = $questionById[(int) $rule['source_question_id']] ?? null;
                if ($source === null) {
                    continue;
                }

                $sourceAnswer = $answers[(string) $source['field_name']] ?? null;

                if ($this->matchesRule($sourceAnswer, (string) $rule['operator'], (string) $rule['compare_value'])) {
                    $visible = true;
                    break;
                }
            }

            $visibility[$id] = $visible;
        }

        return $visibility;
    }

    private function validateAnswers(array $questions, array $visibility, array $answers): array
    {
        $errors = [];
        $validated = [];
        $scoreNps = null;

        foreach ($questions as $question) {
            $id = (int) $question['id'];
            $fieldName = (string) $question['field_name'];
            $label = (string) $question['label'];

            if (!($visibility[$id] ?? true)) {
                continue;
            }

            $value = $answers[$fieldName] ?? null;
            $required = (int) $question['is_required'] === 1;

            if ($required && !$this->hasValue($value)) {
                $errors[$fieldName] = $label . ' e obrigatoria.';
                continue;
            }

            if (!$this->hasValue($value)) {
                continue;
            }

            $normalized = $this->normalizeAnswer($question, $value);
            if (!($normalized['ok'] ?? false)) {
                $errors[$fieldName] = (string) ($normalized['error'] ?? 'Resposta invalida.');
                continue;
            }

            if (($normalized['score_nps'] ?? null) !== null && $scoreNps === null) {
                $scoreNps = (int) $normalized['score_nps'];
            }

            $validated[] = [
                'question_id' => $id,
                'answer_text' => $normalized['answer_text'] ?? null,
                'answer_number' => $normalized['answer_number'] ?? null,
                'answer_json' => $normalized['answer_json'] ?? null,
            ];
        }

        return ['errors' => $errors, 'validated' => $validated, 'score_nps' => $scoreNps];
    }

    private function persist(array $survey, array $meta, array $validated, ?int $scoreNps): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $submissionStmt = $pdo->prepare(
                'INSERT INTO submissions (
                    survey_id, project_id, trigger_event, source_url, user_identifier,
                    session_identifier, user_agent, ip_hash, score_nps, is_completed
                 ) VALUES (
                    :survey_id, :project_id, :trigger_event, :source_url, :user_identifier,
                    :session_identifier, :user_agent, :ip_hash, :score_nps, :is_completed
                 )'
            );

            $submissionStmt->execute([
                'survey_id' => (int) $survey['id'],
                'project_id' => (int) $survey['project_id'],
                'trigger_event' => $meta['trigger_event'],
                'source_url' => $meta['source_url'] !== '' ? $meta['source_url'] : null,
                'user_identifier' => $meta['user_identifier'] !== '' ? $meta['user_identifier'] : null,
                'session_identifier' => $meta['session_identifier'] !== '' ? $meta['session_identifier'] : null,
                'user_agent' => $meta['user_agent'] !== '' ? $meta['user_agent'] : null,
                'ip_hash' => $meta['ip_hash'],
                'score_nps' => $scoreNps,
                'is_completed' => 1,
            ]);

            $submissionId = (int) $pdo->lastInsertId();

            $answerStmt = $pdo->prepare(
                'INSERT INTO submission_answers (
                    submission_id, question_id, answer_text, answer_number, answer_json
                 ) VALUES (
                    :submission_id, :question_id, :answer_text, :answer_number, :answer_json
                 )'
            );

            foreach ($validated as $answer) {
                $answerStmt->execute([
                    'submission_id' => $submissionId,
                    'question_id' => (int) $answer['question_id'],
                    'answer_text' => $answer['answer_text'],
                    'answer_number' => $answer['answer_number'],
                    'answer_json' => $answer['answer_json'],
                ]);
            }

            $pdo->commit();
            return $submissionId;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    private function matchesRule(mixed $sourceAnswer, string $operator, string $compareValue): bool
    {
        if ($sourceAnswer === null) {
            return false;
        }

        if (is_array($sourceAnswer)) {
            $sourceAnswer = array_values(array_map(static fn (mixed $i): string => trim((string) $i), $sourceAnswer));
        }

        if (in_array($operator, ['lt', 'lte', 'gt', 'gte'], true)) {
            if (!is_numeric((string) $sourceAnswer) || !is_numeric($compareValue)) {
                return false;
            }

            $src = (float) $sourceAnswer;
            $cmp = (float) $compareValue;

            return match ($operator) {
                'lt' => $src < $cmp,
                'lte' => $src <= $cmp,
                'gt' => $src > $cmp,
                'gte' => $src >= $cmp,
                default => false,
            };
        }

        if ($operator === 'contains') {
            if (is_array($sourceAnswer)) {
                return in_array($compareValue, $sourceAnswer, true);
            }
            return str_contains(mb_strtolower((string) $sourceAnswer), mb_strtolower($compareValue));
        }

        $sourceValue = is_array($sourceAnswer) ? json_encode($sourceAnswer) : (string) $sourceAnswer;

        return match ($operator) {
            'eq' => $sourceValue === $compareValue,
            'neq' => $sourceValue !== $compareValue,
            default => false,
        };
    }

    private function hasValue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_array($value)) {
            return count($value) > 0;
        }

        return trim((string) $value) !== '';
    }

    private function normalizeAnswer(array $question, mixed $value): array
    {
        if (!$value && $value !== 0 && $value !== '0') {
            return ['ok' => true, 'answer_text' => null, 'answer_number' => null, 'answer_json' => null, 'score_nps' => null];
        }

        $type = (string) $question['question_type'];
        $options = array_values(array_map(
            static fn (mixed $item): string => trim((string) $item),
            (array) ($question['options'] ?? [])
        ));

        if ($type === 'score_0_10') {
            if (!is_numeric((string) $value)) {
                return ['ok' => false, 'error' => 'A nota deve ser numerica (0-10).'];
            }
            $number = (float) $value;
            if ($number < 0 || $number > 10) {
                return ['ok' => false, 'error' => 'A nota deve estar entre 0 e 10.'];
            }
            return ['ok' => true, 'answer_text' => null, 'answer_number' => $number, 'answer_json' => null, 'score_nps' => (int) round($number)];
        }

        if ($type === 'stars_0_5') {
            if (!is_numeric((string) $value)) {
                return ['ok' => false, 'error' => 'As estrelas devem ser numericas (0-5).'];
            }
            $number = (float) $value;
            if ($number < 0 || $number > 5) {
                return ['ok' => false, 'error' => 'As estrelas devem estar entre 0 e 5.'];
            }
            return ['ok' => true, 'answer_text' => null, 'answer_number' => $number, 'answer_json' => null, 'score_nps' => (int) round($number * 2)];
        }

        if ($type === 'checkbox') {
            $values = is_array($value)
                ? array_values($value)
                : array_values(array_filter(array_map('trim', explode(',', (string) $value)), static fn (string $i): bool => $i !== ''));

            foreach ($values as $item) {
                if (!in_array((string) $item, $options, true)) {
                    return ['ok' => false, 'error' => 'Opcao invalida para checkbox.'];
                }
            }

            return ['ok' => true, 'answer_text' => null, 'answer_number' => null, 'answer_json' => json_encode($values, JSON_UNESCAPED_UNICODE), 'score_nps' => null];
        }

        if (in_array($type, ['select', 'radio'], true)) {
            $selected = trim((string) $value);
            if (!in_array($selected, $options, true)) {
                return ['ok' => false, 'error' => 'Opcao invalida para ' . $type . '.'];
            }
            return ['ok' => true, 'answer_text' => $selected, 'answer_number' => null, 'answer_json' => null, 'score_nps' => null];
        }

        return ['ok' => true, 'answer_text' => trim((string) $value), 'answer_number' => null, 'answer_json' => null, 'score_nps' => null];
    }
}
