<?php

declare(strict_types=1);

use App\Domain\Surveys\SubmissionHandler;
use App\Domain\Surveys\SurveyRepository;
use App\Domain\Surveys\TriggerEventLogRepository;
use App\Infrastructure\Database;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return static function (App $app): void {
    $withCors = static function (Response $response): Response {
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    };

    $json = static function (Response $response, array $payload, int $status = 200) use ($withCors): Response {
        $response->getBody()->write((string) json_encode($payload));
        return $withCors($response)
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    };

    $app->options('/api/widget/survey', static function (Request $request, Response $response) use ($withCors): Response {
        return $withCors($response)->withStatus(204);
    });

    $app->options('/api/widget/submissions', static function (Request $request, Response $response) use ($withCors): Response {
        return $withCors($response)->withStatus(204);
    });

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

    $app->get('/api/showcase/stats', static function (Request $request, Response $response) use ($json): Response {
        $pdo = Database::connection();

        $projects = (int) $pdo->query('SELECT COUNT(1) FROM projects')->fetchColumn();
        $surveys = (int) $pdo->query('SELECT COUNT(1) FROM surveys')->fetchColumn();
        $submissions = (int) $pdo->query('SELECT COUNT(1) FROM submissions')->fetchColumn();
        $avgNpsRaw = $pdo->query('SELECT AVG(score_nps) FROM submissions WHERE score_nps IS NOT NULL')->fetchColumn();
        $avgNps = $avgNpsRaw !== null ? round((float) $avgNpsRaw, 2) : null;

        $apiResponse = $json($response, [
            'success' => true,
            'data' => [
                'projects' => $projects,
                'surveys' => $surveys,
                'submissions' => $submissions,
                'avg_nps' => $avgNps,
            ],
            'errors' => [],
        ]);

        return $apiResponse
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Expires', '0');
    });

    $app->get('/api/widget/survey', static function (Request $request, Response $response) use ($json): Response {
        $params = $request->getQueryParams();
        $publicKey = trim((string) ($params['public_key'] ?? ''));
        $triggerEvent = trim((string) ($params['trigger_event'] ?? ''));
        $sourceUrl = trim((string) ($params['source_url'] ?? $request->getHeaderLine('Referer')));
        $userIdentifier = trim((string) ($params['user_identifier'] ?? ''));

        if ($publicKey === '' || $triggerEvent === '') {
            return $json($response, [
                'success' => false,
                'data' => null,
                'errors' => ['public_key e trigger_event sao obrigatorios.'],
            ], 422);
        }

        if (!preg_match('/^nps_pk_[a-zA-Z0-9_]+$/', $publicKey)) {
            return $json($response, [
                'success' => false,
                'data' => null,
                'errors' => ['Chave de projeto invalida.'],
            ], 422);
        }

        $repository = new SurveyRepository();
        $triggerLogRepository = new TriggerEventLogRepository();
        $survey = $repository->findPublishedByProjectKeyAndTrigger($publicKey, $triggerEvent);

        if ($survey === null) {
            $triggerLogRepository->log($publicKey, $triggerEvent, $sourceUrl, $userIdentifier, null);

            return $json($response, [
                'success' => false,
                'data' => null,
                'errors' => ['Nenhuma pesquisa publicada foi associada ao gatilho informado.'],
            ], 404);
        }

        $triggerLogRepository->log(
            $publicKey,
            $triggerEvent,
            $sourceUrl,
            $userIdentifier,
            (int) $survey['id'],
            (int) $survey['project_id']
        );

        $schema = (new SubmissionHandler())->loadSchema((int) $survey['id']);

        return $json($response, [
            'success' => true,
            'data' => [
                'id' => (int) $survey['id'],
                'project_id' => (int) $survey['project_id'],
                'name' => (string) $survey['name'],
                'slug' => (string) $survey['slug'],
                'status' => (string) $survey['status'],
                'trigger_event' => (string) $survey['trigger_event'],
                'requested_trigger_event' => $triggerEvent,
                'fallback_applied' => false,
                'title' => $survey['title'],
                'description' => $survey['description'],
                'questions' => $schema['questions'],
                'rules' => $schema['rules'],
            ],
            'errors' => [],
        ]);
    });

    $app->post('/api/widget/submissions', static function (Request $request, Response $response) use ($json): Response {
        $payload = (array) $request->getParsedBody();

        $publicKey = trim((string) ($payload['public_key'] ?? ''));
        $triggerEvent = trim((string) ($payload['trigger_event'] ?? ''));
        $answersInput = $payload['answers'] ?? [];

        if ($publicKey === '' || $triggerEvent === '' || !is_array($answersInput)) {
            return $json($response, [
                'success' => false,
                'data' => null,
                'errors' => ['public_key, trigger_event e answers (objeto) sao obrigatorios.'],
            ], 422);
        }

        if (!preg_match('/^nps_pk_[a-zA-Z0-9_]+$/', $publicKey)) {
            return $json($response, [
                'success' => false,
                'data' => null,
                'errors' => ['Chave de projeto invalida.'],
            ], 422);
        }

        $ip = (string) ($request->getServerParams()['REMOTE_ADDR'] ?? '');
        $ipHash = $ip !== '' ? hash('sha256', $ip) : null;

        if ($ipHash !== null) {
            $rateLimitStmt = Database::connection()->prepare(
                'SELECT COUNT(1) FROM submissions WHERE ip_hash = :ip_hash AND created_at >= datetime(\'now\', \'-1 minute\')'
            );
            $rateLimitStmt->execute(['ip_hash' => $ipHash]);

            if ((int) $rateLimitStmt->fetchColumn() >= 10) {
                return $json($response, [
                    'success' => false,
                    'data' => null,
                    'errors' => ['Muitas requisicoes. Tente novamente em breve.'],
                ], 429);
            }
        }

        $survey = (new SurveyRepository())->findPublishedByProjectKeyAndTrigger($publicKey, $triggerEvent);
        if ($survey === null) {
            (new TriggerEventLogRepository())->log(
                $publicKey,
                $triggerEvent,
                trim((string) ($payload['source_url'] ?? '')),
                trim((string) ($payload['user_identifier'] ?? '')),
                null
            );

            return $json($response, [
                'success' => false,
                'data' => null,
                'errors' => ['Pesquisa publicada nao encontrada para esta chave e gatilho.'],
            ], 404);
        }

        $meta = [
            'trigger_event' => $triggerEvent,
            'source_url' => trim((string) ($payload['source_url'] ?? '')),
            'user_identifier' => trim((string) ($payload['user_identifier'] ?? '')),
            'session_identifier' => trim((string) ($payload['session_identifier'] ?? '')),
            'user_agent' => trim($request->getHeaderLine('User-Agent')),
            'ip_hash' => $ipHash,
        ];

        try {
            $result = (new SubmissionHandler())->handle($survey, $answersInput, $meta);
        } catch (\Throwable) {
            return $json($response, [
                'success' => false,
                'data' => null,
                'errors' => ['Falha ao persistir submissao.'],
            ], 500);
        }

        if (!$result['ok']) {
            return $json($response, [
                'success' => false,
                'data' => null,
                'errors' => $result['errors'],
            ], 422);
        }

        return $json($response, [
            'success' => true,
            'data' => [
                'submission_id' => $result['submission_id'],
                'survey_id' => (int) $survey['id'],
                'project_id' => (int) $survey['project_id'],
                'trigger_event' => $triggerEvent,
                'stored_answers' => $result['stored_answers'],
            ],
            'errors' => [],
        ], 201);
    });
};
