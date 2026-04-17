<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\Csrf;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class CsrfMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = strtoupper($request->getMethod());
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $handler->handle($request);
        }

        $path = $request->getUri()->getPath();
        if (str_starts_with($path, '/api/')) {
            return $handler->handle($request);
        }

        $parsedBody = $request->getParsedBody();
        $bodyToken = '';
        if (is_array($parsedBody)) {
            $bodyToken = trim((string) ($parsedBody['_csrf'] ?? ''));
        }

        $headerToken = trim($request->getHeaderLine('X-CSRF-Token'));
        $token = $bodyToken !== '' ? $bodyToken : $headerToken;

        if (Csrf::isValid($token)) {
            return $handler->handle($request);
        }

        $response = new Response(419);
        $isHtmx = strtolower($request->getHeaderLine('HX-Request')) === 'true';

        if ($isHtmx) {
            $response->getBody()->write('<div class="alert alert-danger p-15-all" role="alert">Sessao expirada ou token CSRF invalido. Recarregue a pagina e tente novamente.</div>');
            return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        }

        $response->getBody()->write('<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>CSRF invalido</title></head><body><h1>CSRF invalido</h1><p>Sessao expirada ou token CSRF invalido. Recarregue a pagina e tente novamente.</p></body></html>');
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
