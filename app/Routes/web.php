<?php

declare(strict_types=1);

use App\Auth\AdminAuth;
use App\Domain\Projects\ProjectRepository;
use App\Domain\Questions\QuestionRepository;
use App\Domain\Questions\SurveyRuleRepository;
use App\Domain\Surveys\AnalyticsRepository;
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
    $questionTypeOptions = ['score_0_10', 'stars_0_5', 'text', 'select', 'checkbox', 'radio'];
    $ruleOperatorOptions = ['lt', 'lte', 'gt', 'gte', 'eq', 'neq', 'contains'];

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

    $app->group('/admin', static function (RouteCollectorProxy $group) use ($renderTemplate, $statusOptions, $triggerOptions, $questionTypeOptions, $ruleOperatorOptions): void {
        $group->get('', static function (Request $request, Response $response) use ($renderTemplate): Response {
            $content = $renderTemplate('admin/layout.php', [
                'user' => AdminAuth::user(),
                'flashMessages' => Flash::pull(),
            ]);

            $response->getBody()->write($content);
            return $response;
        });

        $group->get('/partials/dashboard', static function (Request $request, Response $response) use ($renderTemplate): Response {
            $analyticsRepository = new AnalyticsRepository();
            $query = $request->getQueryParams();

            $filters = [
                'project_id' => (int) ($query['project_id'] ?? 0),
                'trigger_event' => trim((string) ($query['trigger_event'] ?? '')),
                'from_date' => trim((string) ($query['from_date'] ?? '')),
                'to_date' => trim((string) ($query['to_date'] ?? '')),
            ];

            $content = $renderTemplate('admin/partials/dashboard.php', [
                'user' => AdminAuth::user(),
                'metrics' => $analyticsRepository->metrics($filters),
                'recentSubmissions' => $analyticsRepository->recentSubmissions($filters),
                'projects' => $analyticsRepository->listProjects(),
                'triggerEvents' => $analyticsRepository->listTriggerEvents(),
                'filters' => $filters,
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

        $group->get('/partials/questions', static function (Request $request, Response $response) use ($renderTemplate, $questionTypeOptions, $ruleOperatorOptions): Response {
            $surveyId = (int) (($request->getQueryParams()['survey_id'] ?? 0));
            $surveyRepository = new SurveyRepository();
            $questionRepository = new QuestionRepository();
            $ruleRepository = new SurveyRuleRepository();

            $survey = $surveyRepository->findById($surveyId);
            if ($survey === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Pesquisa nao encontrada.</div>');
                return $response->withStatus(404);
            }

            $questions = $questionRepository->listBySurvey($surveyId);
            $rules = $ruleRepository->listBySurvey($surveyId);

            $content = $renderTemplate('admin/partials/questions.php', [
                'survey' => $survey,
                'questions' => $questions,
                'rules' => $rules,
                'questionTypeOptions' => $questionTypeOptions,
                'ruleOperatorOptions' => $ruleOperatorOptions,
                'errorMessage' => null,
                'flashMessages' => Flash::pull(),
            ]);

            $response->getBody()->write($content);
            return $response;
        });

        $group->get('/questions/form', static function (Request $request, Response $response) use ($renderTemplate, $questionTypeOptions): Response {
            $surveyId = (int) (($request->getQueryParams()['survey_id'] ?? 0));
            $surveyRepository = new SurveyRepository();
            $survey = $surveyRepository->findById($surveyId);
            if ($survey === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Pesquisa nao encontrada.</div>');
                return $response->withStatus(404);
            }

            $content = $renderTemplate('admin/partials/question_form.php', [
                'survey' => $survey,
                'question' => [
                    'id' => null,
                    'label' => '',
                    'field_name' => '',
                    'question_type' => 'text',
                    'is_required' => 0,
                    'placeholder' => '',
                    'help_text' => '',
                    'options' => [],
                ],
                'questionTypeOptions' => $questionTypeOptions,
                'errorMessage' => null,
            ]);

            $response->getBody()->write($content);
            return $response;
        });

        $group->get('/questions/form/{id}', static function (Request $request, Response $response, array $args) use ($renderTemplate, $questionTypeOptions): Response {
            $questionRepository = new QuestionRepository();
            $surveyRepository = new SurveyRepository();
            $question = $questionRepository->findById((int) ($args['id'] ?? 0));

            if ($question === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Pergunta nao encontrada.</div>');
                return $response->withStatus(404);
            }

            $survey = $surveyRepository->findById((int) $question['survey_id']);
            if ($survey === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Pesquisa nao encontrada.</div>');
                return $response->withStatus(404);
            }

            $content = $renderTemplate('admin/partials/question_form.php', [
                'survey' => $survey,
                'question' => $question,
                'questionTypeOptions' => $questionTypeOptions,
                'errorMessage' => null,
            ]);

            $response->getBody()->write($content);
            return $response;
        });

        $group->post('/questions', static function (Request $request, Response $response) use ($renderTemplate, $questionTypeOptions, $ruleOperatorOptions): Response {
            $surveyRepository = new SurveyRepository();
            $questionRepository = new QuestionRepository();
            $ruleRepository = new SurveyRuleRepository();
            $input = (array) $request->getParsedBody();

            $surveyId = (int) ($input['survey_id'] ?? 0);
            $survey = $surveyRepository->findById($surveyId);
            if ($survey === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Pesquisa nao encontrada.</div>');
                return $response->withStatus(404);
            }

            $label = trim((string) ($input['label'] ?? ''));
            $fieldName = strtolower(trim((string) ($input['field_name'] ?? '')));
            $questionType = trim((string) ($input['question_type'] ?? ''));
            $isRequired = isset($input['is_required']) ? 1 : 0;
            $placeholder = trim((string) ($input['placeholder'] ?? ''));
            $helpText = trim((string) ($input['help_text'] ?? ''));

            $rawOptions = trim((string) ($input['options_text'] ?? ''));
            $options = array_values(array_filter(array_map(
                static fn (string $line): string => trim($line),
                preg_split('/\R/u', $rawOptions) ?: []
            ), static fn (string $line): bool => $line !== ''));

            if ($label === '' || $fieldName === '' || $questionType === '') {
                $content = $renderTemplate('admin/partials/questions.php', [
                    'survey' => $survey,
                    'questions' => $questionRepository->listBySurvey($surveyId),
                    'rules' => $ruleRepository->listBySurvey($surveyId),
                    'questionTypeOptions' => $questionTypeOptions,
                    'ruleOperatorOptions' => $ruleOperatorOptions,
                    'errorMessage' => 'Label, campo tecnico e tipo sao obrigatorios.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if (!preg_match('/^[a-z][a-z0-9_]*$/', $fieldName)) {
                $content = $renderTemplate('admin/partials/questions.php', [
                    'survey' => $survey,
                    'questions' => $questionRepository->listBySurvey($surveyId),
                    'rules' => $ruleRepository->listBySurvey($surveyId),
                    'questionTypeOptions' => $questionTypeOptions,
                    'ruleOperatorOptions' => $ruleOperatorOptions,
                    'errorMessage' => 'Campo tecnico invalido. Use letras minusculas, numeros e underscore, iniciando por letra.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if (!in_array($questionType, $questionTypeOptions, true)) {
                $content = $renderTemplate('admin/partials/questions.php', [
                    'survey' => $survey,
                    'questions' => $questionRepository->listBySurvey($surveyId),
                    'rules' => $ruleRepository->listBySurvey($surveyId),
                    'questionTypeOptions' => $questionTypeOptions,
                    'ruleOperatorOptions' => $ruleOperatorOptions,
                    'errorMessage' => 'Tipo de pergunta invalido.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if ($questionRepository->fieldNameExists($surveyId, $fieldName)) {
                $content = $renderTemplate('admin/partials/questions.php', [
                    'survey' => $survey,
                    'questions' => $questionRepository->listBySurvey($surveyId),
                    'rules' => $ruleRepository->listBySurvey($surveyId),
                    'questionTypeOptions' => $questionTypeOptions,
                    'ruleOperatorOptions' => $ruleOperatorOptions,
                    'errorMessage' => 'Ja existe pergunta com este campo tecnico nesta pesquisa.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(409);
            }

            $isOptionType = in_array($questionType, ['select', 'checkbox', 'radio'], true);
            if ($isOptionType && count($options) === 0) {
                $content = $renderTemplate('admin/partials/questions.php', [
                    'survey' => $survey,
                    'questions' => $questionRepository->listBySurvey($surveyId),
                    'rules' => $ruleRepository->listBySurvey($surveyId),
                    'questionTypeOptions' => $questionTypeOptions,
                    'ruleOperatorOptions' => $ruleOperatorOptions,
                    'errorMessage' => 'Tipos select/checkbox/radio exigem pelo menos uma opcao.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            $scaleMin = null;
            $scaleMax = null;
            if ($questionType === 'score_0_10') {
                $scaleMin = 0;
                $scaleMax = 10;
            } elseif ($questionType === 'stars_0_5') {
                $scaleMin = 0;
                $scaleMax = 5;
            }

            $questionRepository->create([
                'survey_id' => $surveyId,
                'label' => $label,
                'field_name' => $fieldName,
                'question_type' => $questionType,
                'is_required' => $isRequired,
                'placeholder' => $placeholder === '' ? null : $placeholder,
                'help_text' => $helpText === '' ? null : $helpText,
                'options_json' => $isOptionType ? json_encode($options, JSON_UNESCAPED_UNICODE) : null,
                'scale_min' => $scaleMin,
                'scale_max' => $scaleMax,
            ]);

            Flash::add('success', 'Pergunta criada com sucesso.');

            $content = $renderTemplate('admin/partials/questions.php', [
                'survey' => $survey,
                'questions' => $questionRepository->listBySurvey($surveyId),
                'rules' => $ruleRepository->listBySurvey($surveyId),
                'questionTypeOptions' => $questionTypeOptions,
                'ruleOperatorOptions' => $ruleOperatorOptions,
                'errorMessage' => null,
                'flashMessages' => Flash::pull(),
            ]);
            $response->getBody()->write($content);
            return $response;
        });

        $group->post('/questions/{id}', static function (Request $request, Response $response, array $args) use ($renderTemplate, $questionTypeOptions, $ruleOperatorOptions): Response {
            $surveyRepository = new SurveyRepository();
            $questionRepository = new QuestionRepository();
            $ruleRepository = new SurveyRuleRepository();
            $question = $questionRepository->findById((int) ($args['id'] ?? 0));
            if ($question === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Pergunta nao encontrada.</div>');
                return $response->withStatus(404);
            }

            $surveyId = (int) $question['survey_id'];
            $survey = $surveyRepository->findById($surveyId);
            if ($survey === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Pesquisa nao encontrada.</div>');
                return $response->withStatus(404);
            }

            $input = (array) $request->getParsedBody();
            $label = trim((string) ($input['label'] ?? ''));
            $fieldName = strtolower(trim((string) ($input['field_name'] ?? '')));
            $questionType = trim((string) ($input['question_type'] ?? ''));
            $isRequired = isset($input['is_required']) ? 1 : 0;
            $placeholder = trim((string) ($input['placeholder'] ?? ''));
            $helpText = trim((string) ($input['help_text'] ?? ''));
            $rawOptions = trim((string) ($input['options_text'] ?? ''));
            $options = array_values(array_filter(array_map(
                static fn (string $line): string => trim($line),
                preg_split('/\R/u', $rawOptions) ?: []
            ), static fn (string $line): bool => $line !== ''));

            if ($label === '' || $fieldName === '' || $questionType === '') {
                $content = $renderTemplate('admin/partials/questions.php', [
                    'survey' => $survey,
                    'questions' => $questionRepository->listBySurvey($surveyId),
                    'rules' => $ruleRepository->listBySurvey($surveyId),
                    'questionTypeOptions' => $questionTypeOptions,
                    'ruleOperatorOptions' => $ruleOperatorOptions,
                    'errorMessage' => 'Label, campo tecnico e tipo sao obrigatorios.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if (!preg_match('/^[a-z][a-z0-9_]*$/', $fieldName)) {
                $content = $renderTemplate('admin/partials/questions.php', [
                    'survey' => $survey,
                    'questions' => $questionRepository->listBySurvey($surveyId),
                    'rules' => $ruleRepository->listBySurvey($surveyId),
                    'questionTypeOptions' => $questionTypeOptions,
                    'ruleOperatorOptions' => $ruleOperatorOptions,
                    'errorMessage' => 'Campo tecnico invalido. Use letras minusculas, numeros e underscore, iniciando por letra.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if (!in_array($questionType, $questionTypeOptions, true)) {
                $content = $renderTemplate('admin/partials/questions.php', [
                    'survey' => $survey,
                    'questions' => $questionRepository->listBySurvey($surveyId),
                    'rules' => $ruleRepository->listBySurvey($surveyId),
                    'questionTypeOptions' => $questionTypeOptions,
                    'ruleOperatorOptions' => $ruleOperatorOptions,
                    'errorMessage' => 'Tipo de pergunta invalido.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if ($questionRepository->fieldNameExists($surveyId, $fieldName, (int) $question['id'])) {
                $content = $renderTemplate('admin/partials/questions.php', [
                    'survey' => $survey,
                    'questions' => $questionRepository->listBySurvey($surveyId),
                    'rules' => $ruleRepository->listBySurvey($surveyId),
                    'questionTypeOptions' => $questionTypeOptions,
                    'ruleOperatorOptions' => $ruleOperatorOptions,
                    'errorMessage' => 'Ja existe pergunta com este campo tecnico nesta pesquisa.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(409);
            }

            $isOptionType = in_array($questionType, ['select', 'checkbox', 'radio'], true);
            if ($isOptionType && count($options) === 0) {
                $content = $renderTemplate('admin/partials/questions.php', [
                    'survey' => $survey,
                    'questions' => $questionRepository->listBySurvey($surveyId),
                    'rules' => $ruleRepository->listBySurvey($surveyId),
                    'questionTypeOptions' => $questionTypeOptions,
                    'ruleOperatorOptions' => $ruleOperatorOptions,
                    'errorMessage' => 'Tipos select/checkbox/radio exigem pelo menos uma opcao.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            $scaleMin = null;
            $scaleMax = null;
            if ($questionType === 'score_0_10') {
                $scaleMin = 0;
                $scaleMax = 10;
            } elseif ($questionType === 'stars_0_5') {
                $scaleMin = 0;
                $scaleMax = 5;
            }

            $questionRepository->update((int) $question['id'], [
                'label' => $label,
                'field_name' => $fieldName,
                'question_type' => $questionType,
                'is_required' => $isRequired,
                'placeholder' => $placeholder === '' ? null : $placeholder,
                'help_text' => $helpText === '' ? null : $helpText,
                'options_json' => $isOptionType ? json_encode($options, JSON_UNESCAPED_UNICODE) : null,
                'scale_min' => $scaleMin,
                'scale_max' => $scaleMax,
            ]);

            Flash::add('success', 'Pergunta atualizada com sucesso.');

            $content = $renderTemplate('admin/partials/questions.php', [
                'survey' => $survey,
                'questions' => $questionRepository->listBySurvey($surveyId),
                'rules' => $ruleRepository->listBySurvey($surveyId),
                'questionTypeOptions' => $questionTypeOptions,
                'ruleOperatorOptions' => $ruleOperatorOptions,
                'errorMessage' => null,
                'flashMessages' => Flash::pull(),
            ]);
            $response->getBody()->write($content);
            return $response;
        });

        $group->post('/questions/{id}/delete', static function (Request $request, Response $response, array $args) use ($renderTemplate, $questionTypeOptions, $ruleOperatorOptions): Response {
            $surveyRepository = new SurveyRepository();
            $questionRepository = new QuestionRepository();
            $ruleRepository = new SurveyRuleRepository();
            $question = $questionRepository->findById((int) ($args['id'] ?? 0));
            if ($question === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Pergunta nao encontrada.</div>');
                return $response->withStatus(404);
            }

            $surveyId = (int) $question['survey_id'];
            $survey = $surveyRepository->findById($surveyId);
            if ($survey === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Pesquisa nao encontrada.</div>');
                return $response->withStatus(404);
            }

            $questionRepository->delete((int) $question['id']);
            Flash::add('success', 'Pergunta removida com sucesso.');

            $content = $renderTemplate('admin/partials/questions.php', [
                'survey' => $survey,
                'questions' => $questionRepository->listBySurvey($surveyId),
                'rules' => $ruleRepository->listBySurvey($surveyId),
                'questionTypeOptions' => $questionTypeOptions,
                'ruleOperatorOptions' => $ruleOperatorOptions,
                'errorMessage' => null,
                'flashMessages' => Flash::pull(),
            ]);
            $response->getBody()->write($content);
            return $response;
        });

        $group->post('/questions/{id}/move', static function (Request $request, Response $response, array $args) use ($renderTemplate, $questionTypeOptions, $ruleOperatorOptions): Response {
            $surveyRepository = new SurveyRepository();
            $questionRepository = new QuestionRepository();
            $ruleRepository = new SurveyRuleRepository();
            $question = $questionRepository->findById((int) ($args['id'] ?? 0));
            if ($question === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Pergunta nao encontrada.</div>');
                return $response->withStatus(404);
            }

            $surveyId = (int) $question['survey_id'];
            $survey = $surveyRepository->findById($surveyId);
            if ($survey === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Pesquisa nao encontrada.</div>');
                return $response->withStatus(404);
            }

            $direction = trim((string) (((array) $request->getParsedBody())['direction'] ?? ''));
            if (in_array($direction, ['up', 'down'], true)) {
                $questionRepository->move((int) $question['id'], $direction);
                Flash::add('success', 'Ordem da pergunta atualizada.');
            }

            $content = $renderTemplate('admin/partials/questions.php', [
                'survey' => $survey,
                'questions' => $questionRepository->listBySurvey($surveyId),
                'rules' => $ruleRepository->listBySurvey($surveyId),
                'questionTypeOptions' => $questionTypeOptions,
                'ruleOperatorOptions' => $ruleOperatorOptions,
                'errorMessage' => null,
                'flashMessages' => Flash::pull(),
            ]);
            $response->getBody()->write($content);
            return $response;
        });

        $group->post('/rules', static function (Request $request, Response $response) use ($renderTemplate, $questionTypeOptions, $ruleOperatorOptions): Response {
            $surveyRepository = new SurveyRepository();
            $questionRepository = new QuestionRepository();
            $ruleRepository = new SurveyRuleRepository();
            $input = (array) $request->getParsedBody();

            $surveyId = (int) ($input['survey_id'] ?? 0);
            $survey = $surveyRepository->findById($surveyId);
            if ($survey === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Pesquisa nao encontrada.</div>');
                return $response->withStatus(404);
            }

            $sourceQuestionId = (int) ($input['source_question_id'] ?? 0);
            $targetQuestionId = (int) ($input['target_question_id'] ?? 0);
            $operator = trim((string) ($input['operator'] ?? ''));
            $compareValue = trim((string) ($input['compare_value'] ?? ''));
            $action = trim((string) ($input['action'] ?? 'show'));

            $source = $questionRepository->findById($sourceQuestionId);
            $target = $questionRepository->findById($targetQuestionId);

            if ($source === null || $target === null || (int) $source['survey_id'] !== $surveyId || (int) $target['survey_id'] !== $surveyId) {
                $content = $renderTemplate('admin/partials/questions.php', [
                    'survey' => $survey,
                    'questions' => $questionRepository->listBySurvey($surveyId),
                    'rules' => $ruleRepository->listBySurvey($surveyId),
                    'questionTypeOptions' => $questionTypeOptions,
                    'ruleOperatorOptions' => $ruleOperatorOptions,
                    'errorMessage' => 'Perguntas de origem e destino devem pertencer a mesma pesquisa.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if ($sourceQuestionId === $targetQuestionId) {
                $content = $renderTemplate('admin/partials/questions.php', [
                    'survey' => $survey,
                    'questions' => $questionRepository->listBySurvey($surveyId),
                    'rules' => $ruleRepository->listBySurvey($surveyId),
                    'questionTypeOptions' => $questionTypeOptions,
                    'ruleOperatorOptions' => $ruleOperatorOptions,
                    'errorMessage' => 'Origem e destino da regra nao podem ser iguais.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if (!in_array($operator, $ruleOperatorOptions, true) || $compareValue === '') {
                $content = $renderTemplate('admin/partials/questions.php', [
                    'survey' => $survey,
                    'questions' => $questionRepository->listBySurvey($surveyId),
                    'rules' => $ruleRepository->listBySurvey($surveyId),
                    'questionTypeOptions' => $questionTypeOptions,
                    'ruleOperatorOptions' => $ruleOperatorOptions,
                    'errorMessage' => 'Operador invalido ou valor de comparacao vazio.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            if ($action !== 'show') {
                $content = $renderTemplate('admin/partials/questions.php', [
                    'survey' => $survey,
                    'questions' => $questionRepository->listBySurvey($surveyId),
                    'rules' => $ruleRepository->listBySurvey($surveyId),
                    'questionTypeOptions' => $questionTypeOptions,
                    'ruleOperatorOptions' => $ruleOperatorOptions,
                    'errorMessage' => 'Acao invalida. Apenas show suportado nesta fase.',
                    'flashMessages' => Flash::pull(),
                ]);
                $response->getBody()->write($content);
                return $response->withStatus(422);
            }

            $ruleRepository->create([
                'survey_id' => $surveyId,
                'source_question_id' => $sourceQuestionId,
                'operator' => $operator,
                'compare_value' => $compareValue,
                'target_question_id' => $targetQuestionId,
                'action' => $action,
            ]);

            Flash::add('success', 'Regra condicional criada com sucesso.');

            $content = $renderTemplate('admin/partials/questions.php', [
                'survey' => $survey,
                'questions' => $questionRepository->listBySurvey($surveyId),
                'rules' => $ruleRepository->listBySurvey($surveyId),
                'questionTypeOptions' => $questionTypeOptions,
                'ruleOperatorOptions' => $ruleOperatorOptions,
                'errorMessage' => null,
                'flashMessages' => Flash::pull(),
            ]);
            $response->getBody()->write($content);
            return $response;
        });

        $group->post('/rules/{id}/delete', static function (Request $request, Response $response, array $args) use ($renderTemplate, $questionTypeOptions, $ruleOperatorOptions): Response {
            $surveyRepository = new SurveyRepository();
            $questionRepository = new QuestionRepository();
            $ruleRepository = new SurveyRuleRepository();
            $surveyId = (int) (((array) $request->getParsedBody())['survey_id'] ?? 0);
            $survey = $surveyRepository->findById($surveyId);
            if ($survey === null) {
                $response->getBody()->write('<div class="alert alert-danger p-15-all">Pesquisa nao encontrada.</div>');
                return $response->withStatus(404);
            }

            $ruleRepository->delete((int) ($args['id'] ?? 0));
            Flash::add('success', 'Regra removida com sucesso.');

            $content = $renderTemplate('admin/partials/questions.php', [
                'survey' => $survey,
                'questions' => $questionRepository->listBySurvey($surveyId),
                'rules' => $ruleRepository->listBySurvey($surveyId),
                'questionTypeOptions' => $questionTypeOptions,
                'ruleOperatorOptions' => $ruleOperatorOptions,
                'errorMessage' => null,
                'flashMessages' => Flash::pull(),
            ]);
            $response->getBody()->write($content);
            return $response;
        });
    })->add(new AdminAuthMiddleware());
};
