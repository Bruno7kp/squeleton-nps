<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Auth\AdminAuth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class AdminAuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (AdminAuth::check()) {
            return $handler->handle($request);
        }

        $response = new Response(302);
        $response = $response->withHeader('Location', '/login');

        if (strtolower($request->getHeaderLine('HX-Request')) === 'true') {
            $response = $response->withHeader('HX-Redirect', '/login');
        }

        return $response;
    }
}
