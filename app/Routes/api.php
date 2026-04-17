<?php

declare(strict_types=1);

use App\Domain\Surveys\SurveyRepository;
use App\Infrastructure\Database;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return static function (App $app): void {
    $app->get('/api/health', static function (Request $request, Response $response): Response {
        $database = 'down';

        try {
            Database::connection()->query('SELECT 1');
            $database = 'up';
        } catch (Throwable $exception) {
            $database = 'down';
        }

        $payload = [
            'status' => $database === 'up' ? 'ok' : 'degraded',
            'service' => 'nps-api',
            'database' => $database,
            'timestamp' => gmdate('c'),
        ];

        $response->getBody()->write((string) json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/api/widget/survey', static function (Request $request, Response $response): Response {
        $params = $request->getQueryParams();
        $publicKey = trim((string) ($params['public_key'] ?? ''));
        $triggerEvent = trim((string) ($params['trigger_event'] ?? ''));

        if ($publicKey === '' || $triggerEvent === '') {
            $payload = [
                'success' => false,
                'data' => null,
                'errors' => ['public_key e trigger_event sao obrigatorios.'],
            ];

            $response->getBody()->write((string) json_encode($payload));
            return $response
                ->withStatus(422)
                ->withHeader('Content-Type', 'application/json');
        }

        $repository = new SurveyRepository();
        $survey = $repository->findPublishedByProjectKeyAndTrigger($publicKey, $triggerEvent);

        if ($survey === null) {
            $payload = [
                'success' => false,
                'data' => null,
                'errors' => ['Pesquisa publicada nao encontrada para esta chave e gatilho.'],
            ];

            $response->getBody()->write((string) json_encode($payload));
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json');
        }

        $payload = [
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
            ],
            'errors' => [],
        ];

        $response->getBody()->write((string) json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
