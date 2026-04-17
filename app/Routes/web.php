<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return static function (App $app): void {
    $app->get('/', static function (Request $request, Response $response): Response {
        ob_start();
        require dirname(__DIR__, 2) . '/templates/home.php';
        $content = (string) ob_get_clean();

        $response->getBody()->write($content);
        return $response;
    });

    $app->get('/health', static function (Request $request, Response $response): Response {
        $payload = [
            'status' => 'ok',
            'service' => 'nps-web',
            'timestamp' => gmdate('c'),
        ];

        $response->getBody()->write((string) json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
