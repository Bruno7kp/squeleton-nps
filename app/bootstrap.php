<?php

declare(strict_types=1);

use App\Middleware\CsrfMiddleware;
use App\Middleware\SecurityHeadersMiddleware;
use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Throwable;

return static function (App $app): void {
    $projectRoot = dirname(__DIR__);

    if (file_exists($projectRoot . '/.env')) {
        Dotenv::createImmutable($projectRoot)->safeLoad();
    }

    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $app->add(new CsrfMiddleware());
    $app->add(new SecurityHeadersMiddleware());

    $displayErrorDetails = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL);
    $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);

    $errorMiddleware->setDefaultErrorHandler(
        static function (
            ServerRequestInterface $request,
            Throwable $exception,
            bool $displayErrorDetails
        ) use ($app): ResponseInterface {
            $response = $app->getResponseFactory()->createResponse(500);
            $isApiRequest = str_starts_with($request->getUri()->getPath(), '/api/');

            if ($isApiRequest) {
                $payload = [
                    'success' => false,
                    'data' => null,
                    'errors' => [$displayErrorDetails ? $exception->getMessage() : 'Erro interno no servidor.'],
                ];

                $response->getBody()->write((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                return $response->withHeader('Content-Type', 'application/json');
            }

            $safeMessage = $displayErrorDetails
                ? htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8')
                : 'Erro interno no servidor.';

            $response->getBody()->write(
                '<!doctype html>' .
                '<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">' .
                '<title>Erro inesperado</title>' .
                '<style>body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#f8fafc;color:#1f2937;padding:32px}.box{max-width:720px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px}h1{margin:0 0 12px 0;font-size:28px}p{line-height:1.6}</style>' .
                '</head><body><main class="box"><h1>Algo deu errado</h1><p>Desculpe, nao foi possivel processar sua solicitacao agora.</p><p><strong>Detalhes:</strong> ' . $safeMessage . '</p><p><a href="/admin">Voltar ao admin</a></p></main></body></html>'
            );

            return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        }
    );

    (require __DIR__ . '/Routes/web.php')($app);
    (require __DIR__ . '/Routes/api.php')($app);
};
