<?php

declare(strict_types=1);

use App\Domain\Surveys\SurveyRepository;
use App\Infrastructure\Database;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return static function (App $app): void {
    $json = static function (Response $response, array $payload, int $status = 200): Response {
        $response->getBody()->write((string) json_encode($payload));
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    };

    $loadSurveySchema = static function (int $surveyId): array {
        $questionsStmt = Database::connection()->prepare(
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
            $question['scale_min'] = ($question['scale_min'] !== null) ? (int) $question['scale_min'] : null;
            $question['scale_max'] = ($question['scale_max'] !== null) ? (int) $question['scale_max'] : null;
            unset($question['options_json']);
        }

        $rulesStmt = Database::connection()->prepare(
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

        return [
            'questions' => $questions,
            'rules' => $rules,
        ];
    };

    $matchesRule = static function (mixed $sourceAnswer, string $operator, string $compareValue): bool {
        if ($sourceAnswer === null) {
            return false;
        }

        if (is_array($sourceAnswer)) {
            $sourceAnswer = array_values(array_map(static fn (mixed $item): string => trim((string) $item), $sourceAnswer));
        }

        if (in_array($operator, ['lt', 'lte', 'gt', 'gte'], true)) {
            if (!is_numeric((string) $sourceAnswer) || !is_numeric($compareValue)) {
                return false;
            }

            $sourceNumber = (float) $sourceAnswer;
            $compareNumber = (float) $compareValue;

            return match ($operator) {
                'lt' => $sourceNumber < $compareNumber,
                'lte' => $sourceNumber <= $compareNumber,
                'gt' => $sourceNumber > $compareNumber,
                'gte' => $sourceNumber >= $compareNumber,
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
    };

    $hasValue = static function (mixed $value): bool {
        if ($value === null) {
            return false;
        }

        if (is_array($value)) {
            return count($value) > 0;
        }

        return trim((string) $value) !== '';
    };

    $normalizeAnswer = static function (array $question, mixed $value): array {
        if (!$value && $value !== 0 && $value !== '0') {
            return [
                'ok' => true,
                'answer_text' => null,
                'answer_number' => null,
                'answer_json' => null,
                'score_nps' => null,
            ];
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

            return [
                'ok' => true,
                'answer_text' => null,
                'answer_number' => $number,
                'answer_json' => null,
                'score_nps' => (int) round($number),
            ];
        }

        if ($type === 'stars_0_5') {
            if (!is_numeric((string) $value)) {
                return ['ok' => false, 'error' => 'As estrelas devem ser numericas (0-5).'];
            }

            $number = (float) $value;
            if ($number < 0 || $number > 5) {
                return ['ok' => false, 'error' => 'As estrelas devem estar entre 0 e 5.'];
            }

            return [
                'ok' => true,
                'answer_text' => null,
                'answer_number' => $number,
                'answer_json' => null,
                'score_nps' => (int) round($number * 2),
            ];
        }

        if ($type === 'checkbox') {
            $values = is_array($value)
                ? array_values($value)
                : array_values(array_filter(array_map('trim', explode(',', (string) $value)), static fn (string $item): bool => $item !== ''));

            foreach ($values as $item) {
                if (!in_array((string) $item, $options, true)) {
                    return ['ok' => false, 'error' => 'Opcao invalida para checkbox.'];
                }
            }

            return [
                'ok' => true,
                'answer_text' => null,
                'answer_number' => null,
                'answer_json' => json_encode($values, JSON_UNESCAPED_UNICODE),
                'score_nps' => null,
            ];
        }

        if (in_array($type, ['select', 'radio'], true)) {
            $selected = trim((string) $value);
            if (!in_array($selected, $options, true)) {
                return ['ok' => false, 'error' => 'Opcao invalida para ' . $type . '.'];
            }

            return [
                'ok' => true,
                'answer_text' => $selected,
                'answer_number' => null,
                'answer_json' => null,
                'score_nps' => null,
            ];
        }

        return [
            'ok' => true,
            'answer_text' => trim((string) $value),
            'answer_number' => null,
            'answer_json' => null,
            'score_nps' => null,
        ];
    };

    $app->get('/api/health', static function (Request $request, Response $response): Response {
        $database = 'down';

        try {
            Database::connection()->query('SELECT 1');
            $database = 'up';
        } catch (Throwable $exception) {
            $database = 'down';
        }

        return $json($response, [
            'status' => $database === 'up' ? 'ok' : 'degraded',
            'service' => 'nps-api',
            'database' => $database,
            'timestamp' => gmdate('c'),
        ]);
    });

    $app->get('/api/widget/survey', static function (Request $request, Response $response) use ($json, $loadSurveySchema): Response {
        $params = $request->getQueryParams();
        $publicKey = trim((string) ($params['public_key'] ?? ''));
        $triggerEvent = trim((string) ($params['trigger_event'] ?? ''));

        if ($publicKey === '' || $triggerEvent === '') {
            return $json($response, [
                'success' => false,
                'data' => null,
                'errors' => ['public_key e trigger_event sao obrigatorios.'],
            ], 422);
        }

        $repository = new SurveyRepository();
        $survey = $repository->findPublishedByProjectKeyAndTrigger($publicKey, $triggerEvent);

        if ($survey === null) {
            return $json($response, [
                'success' => false,
                'data' => null,
                'errors' => ['Pesquisa publicada nao encontrada para esta chave e gatilho.'],
            ], 404);
        }

        $schema = $loadSurveySchema((int) $survey['id']);

        return $json($response, [
            'success' => true,
            'data' => [
                'id' => (int) $survey['id'],
                'project_id' => (int) $survey['project_id'],
                'name' => (string) $survey['name'],
                'slug' => (string) $survey['slug'],
                'status' => (string) $survey['status'],
                'trigger_event' => (string) $survey['trigger_event'],
                'title' => $survey['title'],
                'description' => $survey['description'],
                'questions' => $schema['questions'],
                'rules' => $schema['rules'],
            ],
            'errors' => [],
        ]);
    });

    $app->post('/api/widget/submissions', static function (Request $request, Response $response) use ($json, $loadSurveySchema, $matchesRule, $hasValue, $normalizeAnswer): Response {
        $payload = (array) $request->getParsedBody();

        $publicKey = trim((string) ($payload['public_key'] ?? ''));
        $triggerEvent = trim((string) ($payload['trigger_event'] ?? ''));
        $sourceUrl = trim((string) ($payload['source_url'] ?? ''));
        $userIdentifier = trim((string) ($payload['user_identifier'] ?? ''));
        $sessionIdentifier = trim((string) ($payload['session_identifier'] ?? ''));
        $answersInput = $payload['answers'] ?? [];
        $answers = is_array($answersInput) ? $answersInput : [];

        if ($publicKey === '' || $triggerEvent === '' || !is_array($answersInput)) {
            return $json($response, [
                'success' => false,
                'data' => null,
                'errors' => ['public_key, trigger_event e answers (objeto) sao obrigatorios.'],
            ], 422);
        }

        $surveyRepository = new SurveyRepository();
        $survey = $surveyRepository->findPublishedByProjectKeyAndTrigger($publicKey, $triggerEvent);
        if ($survey === null) {
            return $json($response, [
                'success' => false,
                'data' => null,
                'errors' => ['Pesquisa publicada nao encontrada para esta chave e gatilho.'],
            ], 404);
        }

        $schema = $loadSurveySchema((int) $survey['id']);
        $questions = $schema['questions'];
        $rules = $schema['rules'];

        $questionById = [];
        foreach ($questions as $question) {
            $questionById[(int) $question['id']] = $question;
        }

        $rulesByTarget = [];
        foreach ($rules as $rule) {
            $targetId = (int) $rule['target_question_id'];
            if (!isset($rulesByTarget[$targetId])) {
                $rulesByTarget[$targetId] = [];
            }
            $rulesByTarget[$targetId][] = $rule;
        }

        $visibility = [];
        foreach ($questions as $question) {
            $questionId = (int) $question['id'];
            if (!isset($rulesByTarget[$questionId])) {
                $visibility[$questionId] = true;
                continue;
            }

            $visible = false;
            foreach ($rulesByTarget[$questionId] as $rule) {
                $sourceQuestion = $questionById[(int) $rule['source_question_id']] ?? null;
                if ($sourceQuestion === null) {
                    continue;
                }

                $sourceField = (string) $sourceQuestion['field_name'];
                $sourceAnswer = $answers[$sourceField] ?? null;

                if ($matchesRule($sourceAnswer, (string) $rule['operator'], (string) $rule['compare_value'])) {
                    $visible = true;
                    break;
                }
            }

            $visibility[$questionId] = $visible;
        }

        $validationErrors = [];
        $validatedAnswers = [];
        $scoreNps = null;

        foreach ($questions as $question) {
            $questionId = (int) $question['id'];
            $fieldName = (string) $question['field_name'];
            $questionLabel = (string) $question['label'];
            $visible = $visibility[$questionId] ?? true;

            if (!$visible) {
                continue;
            }

            $value = $answers[$fieldName] ?? null;
            $required = (int) $question['is_required'] === 1;

            if ($required && !$hasValue($value)) {
                $validationErrors[$fieldName] = $questionLabel . ' e obrigatoria.';
                continue;
            }

            if (!$hasValue($value)) {
                continue;
            }

            $normalized = $normalizeAnswer($question, $value);
            if (!($normalized['ok'] ?? false)) {
                $validationErrors[$fieldName] = (string) ($normalized['error'] ?? 'Resposta invalida.');
                continue;
            }

            if (($normalized['score_nps'] ?? null) !== null && $scoreNps === null) {
                $scoreNps = (int) $normalized['score_nps'];
            }

            $validatedAnswers[] = [
                'question_id' => $questionId,
                'answer_text' => $normalized['answer_text'] ?? null,
                'answer_number' => $normalized['answer_number'] ?? null,
                'answer_json' => $normalized['answer_json'] ?? null,
            ];
        }

        if (!empty($validationErrors)) {
            return $json($response, [
                'success' => false,
                'data' => null,
                'errors' => $validationErrors,
            ], 422);
        }

        $pdo = Database::connection();
        $ip = (string) ($request->getServerParams()['REMOTE_ADDR'] ?? '');
        $ipHash = $ip !== '' ? hash('sha256', $ip) : null;
        $userAgent = trim($request->getHeaderLine('User-Agent'));

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
                'trigger_event' => $triggerEvent,
                'source_url' => $sourceUrl === '' ? null : $sourceUrl,
                'user_identifier' => $userIdentifier === '' ? null : $userIdentifier,
                'session_identifier' => $sessionIdentifier === '' ? null : $sessionIdentifier,
                'user_agent' => $userAgent === '' ? null : $userAgent,
                'ip_hash' => $ipHash,
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

            foreach ($validatedAnswers as $answer) {
                $answerStmt->execute([
                    'submission_id' => $submissionId,
                    'question_id' => (int) $answer['question_id'],
                    'answer_text' => $answer['answer_text'],
                    'answer_number' => $answer['answer_number'],
                    'answer_json' => $answer['answer_json'],
                ]);
            }

            $pdo->commit();

            return $json($response, [
                'success' => true,
                'data' => [
                    'submission_id' => $submissionId,
                    'survey_id' => (int) $survey['id'],
                    'project_id' => (int) $survey['project_id'],
                    'trigger_event' => $triggerEvent,
                    'stored_answers' => count($validatedAnswers),
                ],
                'errors' => [],
            ], 201);
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return $json($response, [
                'success' => false,
                'data' => null,
                'errors' => ['Falha ao persistir submissao.'],
            ], 500);
        }
    });
};
