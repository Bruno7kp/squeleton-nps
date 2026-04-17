<?php

declare(strict_types=1);

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
};
