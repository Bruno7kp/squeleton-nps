<?php

declare(strict_types=1);

use App\Auth\AdminAuth;
use App\Domain\Projects\ProjectRepository;
use App\Domain\Surveys\SurveyRepository;
use App\Middleware\AdminAuthMiddleware;
use App\Support\Flash;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app): void {
    $statusOptions = ['draft', 'published'];
    $triggerOptions = ['on_load', 'after_completed_video', 'before_cancel'];

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

    $app->group('/admin', static function (RouteCollectorProxy $group) use ($renderTemplate, $statusOptions, $triggerOptions): void {
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

        $group->get('/partials/projects', static function (Request $request, Response $response) use ($renderTemplate): Response {
            $repository = new ProjectRepository();

            $content = $renderTemplate('admin/partials/projects.php', [
                'projects' => $repository->listAll(),
                'errorMessage' => null,
                'flashMessages' => Flash::pull(),
            ]);

            $response->getBody()->write($content);
            return $response;
        });

        $group->get('/projects/form', static function (Request $request, Response $response) use ($renderTemplate): Response {
            $content = $renderTemplate('admin/partials/project_form.php', [
                'project' => [
                    'id' => null,
                    'name' => '',
                    'slug' => '',
                    'description' => '',
                    'is_active' => 1,
                    'public_key' => null,
                ],
                'errorMessage' => null,
            ]);

            $response->getBody()->write($content);
            return $response;
        });

        $group->get('/projects/form/{id}', static function (Request $request, Response $response, array $args) use ($renderTemplate): Response {
            $repository = new ProjectRepository();
            $project = $repository->findById((int) ($args['id'] ?? 0));

            if ($project === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Projeto nao encontrado.</div>');
                return $response->withStatus(404);
            }

            $content = $renderTemplate('admin/partials/project_form.php', [
                'project' => $project,
                'errorMessage' => null,
            ]);

            $response->getBody()->write($content);
            return $response;
        });

        $group->post('/projects', static function (Request $request, Response $response) use ($renderTemplate): Response {
            $repository = new ProjectRepository();
            $input = (array) $request->getParsedBody();

            $name = trim((string) ($input['name'] ?? ''));
            $slug = strtolower(trim((string) ($input['slug'] ?? '')));
            $description = trim((string) ($input['description'] ?? ''));
            $isActive = isset($input['is_active']) ? 1 : 0;

            if ($name === '' || $slug === '') {
                $content = $renderTemplate('admin/partials/projects.php', [
                    'projects' => $repository->listAll(),
                    'errorMessage' => 'Nome e slug sao obrigatorios.',
                    'flashMessages' => Flash::pull(),
                ]);

                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
                $content = $renderTemplate('admin/partials/projects.php', [
                    'projects' => $repository->listAll(),
                    'errorMessage' => 'Slug invalido. Use apenas letras minusculas, numeros e hifen.',
                    'flashMessages' => Flash::pull(),
                ]);

                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if ($repository->slugExists($slug)) {
                $content = $renderTemplate('admin/partials/projects.php', [
                    'projects' => $repository->listAll(),
                    'errorMessage' => 'Ja existe um projeto com este slug.',
                    'flashMessages' => Flash::pull(),
                ]);

                $response->getBody()->write($content);
                return $response->withStatus(409);
            }

            $publicKey = 'nps_pk_' . bin2hex(random_bytes(8));

            $repository->create([
                'name' => $name,
                'slug' => $slug,
                'public_key' => $publicKey,
                'description' => ($description === '') ? null : $description,
                'is_active' => $isActive,
            ]);

            Flash::add('success', 'Projeto criado com sucesso.');

            $content = $renderTemplate('admin/partials/projects.php', [
                'projects' => $repository->listAll(),
                'errorMessage' => null,
                'flashMessages' => Flash::pull(),
            ]);

            $response->getBody()->write($content);
            return $response;
        });

        $group->post('/projects/{id}', static function (Request $request, Response $response, array $args) use ($renderTemplate): Response {
            $repository = new ProjectRepository();
            $id = (int) ($args['id'] ?? 0);
            $project = $repository->findById($id);

            if ($project === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Projeto nao encontrado.</div>');
                return $response->withStatus(404);
            }

            $input = (array) $request->getParsedBody();
            $name = trim((string) ($input['name'] ?? ''));
            $slug = strtolower(trim((string) ($input['slug'] ?? '')));
            $description = trim((string) ($input['description'] ?? ''));
            $isActive = isset($input['is_active']) ? 1 : 0;

            if ($name === '' || $slug === '') {
                $content = $renderTemplate('admin/partials/projects.php', [
                    'projects' => $repository->listAll(),
                    'errorMessage' => 'Nome e slug sao obrigatorios.',
                    'flashMessages' => Flash::pull(),
                ]);

                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
                $content = $renderTemplate('admin/partials/projects.php', [
                    'projects' => $repository->listAll(),
                    'errorMessage' => 'Slug invalido. Use apenas letras minusculas, numeros e hifen.',
                    'flashMessages' => Flash::pull(),
                ]);

                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if ($repository->slugExists($slug, $id)) {
                $content = $renderTemplate('admin/partials/projects.php', [
                    'projects' => $repository->listAll(),
                    'errorMessage' => 'Ja existe um projeto com este slug.',
                    'flashMessages' => Flash::pull(),
                ]);

                $response->getBody()->write($content);
                return $response->withStatus(409);
            }

            $repository->update($id, [
                'name' => $name,
                'slug' => $slug,
                'description' => ($description === '') ? null : $description,
                'is_active' => $isActive,
            ]);

            Flash::add('success', 'Projeto atualizado com sucesso.');

            $content = $renderTemplate('admin/partials/projects.php', [
                'projects' => $repository->listAll(),
                'errorMessage' => null,
                'flashMessages' => Flash::pull(),
            ]);

            $response->getBody()->write($content);
            return $response;
        });

        $group->get('/partials/surveys', static function (Request $request, Response $response) use ($renderTemplate): Response {
            $repository = new SurveyRepository();

            $content = $renderTemplate('admin/partials/surveys.php', [
                'surveys' => $repository->listWithProject(),
                'errorMessage' => null,
                'flashMessages' => Flash::pull(),
            ]);

            $response->getBody()->write($content);
            return $response;
        });

        $group->get('/surveys/form', static function (Request $request, Response $response) use ($renderTemplate, $statusOptions, $triggerOptions): Response {
            $projectRepository = new ProjectRepository();

            $content = $renderTemplate('admin/partials/survey_form.php', [
                'survey' => [
                    'id' => null,
                    'project_id' => null,
                    'name' => '',
                    'slug' => '',
                    'status' => 'draft',
                    'trigger_event' => 'on_load',
                    'title' => '',
                    'description' => '',
                ],
                'projects' => $projectRepository->listAll(),
                'statusOptions' => $statusOptions,
                'triggerOptions' => $triggerOptions,
                'errorMessage' => null,
            ]);

            $response->getBody()->write($content);
            return $response;
        });

        $group->get('/surveys/form/{id}', static function (Request $request, Response $response, array $args) use ($renderTemplate, $statusOptions, $triggerOptions): Response {
            $projectRepository = new ProjectRepository();
            $surveyRepository = new SurveyRepository();
            $survey = $surveyRepository->findById((int) ($args['id'] ?? 0));

            if ($survey === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Pesquisa nao encontrada.</div>');
                return $response->withStatus(404);
            }

            $content = $renderTemplate('admin/partials/survey_form.php', [
                'survey' => $survey,
                'projects' => $projectRepository->listAll(),
                'statusOptions' => $statusOptions,
                'triggerOptions' => $triggerOptions,
                'errorMessage' => null,
            ]);

            $response->getBody()->write($content);
            return $response;
        });

        $group->post('/surveys', static function (Request $request, Response $response) use ($renderTemplate, $statusOptions, $triggerOptions): Response {
            $projectRepository = new ProjectRepository();
            $surveyRepository = new SurveyRepository();
            $input = (array) $request->getParsedBody();

            $projectId = (int) ($input['project_id'] ?? 0);
            $name = trim((string) ($input['name'] ?? ''));
            $slug = strtolower(trim((string) ($input['slug'] ?? '')));
            $status = trim((string) ($input['status'] ?? 'draft'));
            $triggerEvent = trim((string) ($input['trigger_event'] ?? 'on_load'));
            $title = trim((string) ($input['title'] ?? ''));
            $description = trim((string) ($input['description'] ?? ''));

            $selectedProject = $projectRepository->findById($projectId);

            if ($selectedProject === null || $name === '' || $slug === '') {
                $content = $renderTemplate('admin/partials/surveys.php', [
                    'surveys' => $surveyRepository->listWithProject(),
                    'errorMessage' => 'Projeto, nome e slug sao obrigatorios.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
                $content = $renderTemplate('admin/partials/surveys.php', [
                    'surveys' => $surveyRepository->listWithProject(),
                    'errorMessage' => 'Slug invalido. Use apenas letras minusculas, numeros e hifen.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if (!in_array($status, $statusOptions, true)) {
                $content = $renderTemplate('admin/partials/surveys.php', [
                    'surveys' => $surveyRepository->listWithProject(),
                    'errorMessage' => 'Status invalido. Use draft ou published.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if (!in_array($triggerEvent, $triggerOptions, true)) {
                $content = $renderTemplate('admin/partials/surveys.php', [
                    'surveys' => $surveyRepository->listWithProject(),
                    'errorMessage' => 'Gatilho invalido.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if ($surveyRepository->slugExists($projectId, $slug)) {
                $content = $renderTemplate('admin/partials/surveys.php', [
                    'surveys' => $surveyRepository->listWithProject(),
                    'errorMessage' => 'Ja existe uma pesquisa com este slug para o projeto selecionado.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(409);
            }

            $surveyRepository->create([
                'project_id' => $projectId,
                'name' => $name,
                'slug' => $slug,
                'status' => $status,
                'trigger_event' => $triggerEvent,
                'title' => ($title === '') ? null : $title,
                'description' => ($description === '') ? null : $description,
            ]);

            Flash::add('success', 'Pesquisa criada com sucesso.');

            $content = $renderTemplate('admin/partials/surveys.php', [
                'surveys' => $surveyRepository->listWithProject(),
                'errorMessage' => null,
                'flashMessages' => Flash::pull(),
            ]);
            $response->getBody()->write($content);
            return $response;
        });

        $group->post('/surveys/{id}', static function (Request $request, Response $response, array $args) use ($renderTemplate, $statusOptions, $triggerOptions): Response {
            $projectRepository = new ProjectRepository();
            $surveyRepository = new SurveyRepository();

            $id = (int) ($args['id'] ?? 0);
            $existing = $surveyRepository->findById($id);
            if ($existing === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Pesquisa nao encontrada.</div>');
                return $response->withStatus(404);
            }

            $input = (array) $request->getParsedBody();

            $projectId = (int) ($input['project_id'] ?? 0);
            $name = trim((string) ($input['name'] ?? ''));
            $slug = strtolower(trim((string) ($input['slug'] ?? '')));
            $status = trim((string) ($input['status'] ?? 'draft'));
            $triggerEvent = trim((string) ($input['trigger_event'] ?? 'on_load'));
            $title = trim((string) ($input['title'] ?? ''));
            $description = trim((string) ($input['description'] ?? ''));

            $selectedProject = $projectRepository->findById($projectId);
            if ($selectedProject === null || $name === '' || $slug === '') {
                $content = $renderTemplate('admin/partials/surveys.php', [
                    'surveys' => $surveyRepository->listWithProject(),
                    'errorMessage' => 'Projeto, nome e slug sao obrigatorios.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
                $content = $renderTemplate('admin/partials/surveys.php', [
                    'surveys' => $surveyRepository->listWithProject(),
                    'errorMessage' => 'Slug invalido. Use apenas letras minusculas, numeros e hifen.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if (!in_array($status, $statusOptions, true)) {
                $content = $renderTemplate('admin/partials/surveys.php', [
                    'surveys' => $surveyRepository->listWithProject(),
                    'errorMessage' => 'Status invalido. Use draft ou published.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if (!in_array($triggerEvent, $triggerOptions, true)) {
                $content = $renderTemplate('admin/partials/surveys.php', [
                    'surveys' => $surveyRepository->listWithProject(),
                    'errorMessage' => 'Gatilho invalido.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if ($surveyRepository->slugExists($projectId, $slug, $id)) {
                $content = $renderTemplate('admin/partials/surveys.php', [
                    'surveys' => $surveyRepository->listWithProject(),
                    'errorMessage' => 'Ja existe uma pesquisa com este slug para o projeto selecionado.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(409);
            }

            $surveyRepository->update($id, [
                'project_id' => $projectId,
                'name' => $name,
                'slug' => $slug,
                'status' => $status,
                'trigger_event' => $triggerEvent,
                'title' => ($title === '') ? null : $title,
                'description' => ($description === '') ? null : $description,
            ]);

            Flash::add('success', 'Pesquisa atualizada com sucesso.');

            $content = $renderTemplate('admin/partials/surveys.php', [
                'surveys' => $surveyRepository->listWithProject(),
                'errorMessage' => null,
                'flashMessages' => Flash::pull(),
            ]);
            $response->getBody()->write($content);
            return $response;
        });
    })->add(new AdminAuthMiddleware());
};
