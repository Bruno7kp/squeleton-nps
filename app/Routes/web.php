<?php

declare(strict_types=1);

use App\Auth\AdminAuth;
use App\Middleware\AdminAuthMiddleware;
use App\Support\Flash;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app): void {
    $renderTemplate = static function (string $templatePath, array $data = []): string {
        extract($data, EXTR_SKIP);
        ob_start();
        require dirname(__DIR__, 2) . '/templates/' . ltrim($templatePath, '/');
        return (string) ob_get_clean();
    };

    $redirect = static function (Response $response, string $path): Response {
        return $response->withHeader('Location', $path)->withStatus(302);
    };

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

    $app->get('/login', static function (Request $request, Response $response) use ($renderTemplate): Response {
        if (AdminAuth::check()) {
            return $response->withHeader('Location', '/admin')->withStatus(302);
        }

        $content = $renderTemplate('admin/login.php', [
            'flashMessages' => Flash::pull(),
        ]);

        $response->getBody()->write($content);
        return $response;
    });

    $app->post('/login', static function (Request $request, Response $response) use ($redirect): Response {
        $data = (array) $request->getParsedBody();
        $username = trim((string) ($data['username'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($username === '' || $password === '') {
            Flash::add('error', 'Preencha usuario e senha.');
            return $redirect($response, '/login');
        }

        if (!AdminAuth::attempt($username, $password)) {
            Flash::add('error', 'Credenciais invalidas.');
            return $redirect($response, '/login');
        }

        AdminAuth::login($username);
        Flash::add('success', 'Login realizado com sucesso.');

        return $redirect($response, '/admin');
    });

    $app->post('/logout', static function (Request $request, Response $response) use ($redirect): Response {
        AdminAuth::logout();
        Flash::add('success', 'Sessao encerrada com sucesso.');

        return $redirect($response, '/login');
    });

    $app->group('/admin', static function (RouteCollectorProxy $group) use ($renderTemplate): void {
        $group->get('', static function (Request $request, Response $response) use ($renderTemplate): Response {
            $content = $renderTemplate('admin/layout.php', [
                'user' => AdminAuth::user(),
                'flashMessages' => Flash::pull(),
            ]);

            $response->getBody()->write($content);
            return $response;
        });

        $group->get('/partials/dashboard', static function (Request $request, Response $response) use ($renderTemplate): Response {
            $content = $renderTemplate('admin/partials/dashboard.php', [
                'user' => AdminAuth::user(),
            ]);

            $response->getBody()->write($content);
            return $response;
        });
    })->add(new AdminAuthMiddleware());
};
