<?php
$metrics = $metrics ?? [
    'total_submissions' => 0,
    'completed_submissions' => 0,
    'avg_nps' => null,
    'completion_rate' => 0.0,
];
$recentSubmissions = $recentSubmissions ?? [];
$projects = $projects ?? [];
$surveyOptions = $surveyOptions ?? [];
$filters = $filters ?? ['project_id' => 0, 'survey_id' => 0, 'from_date' => '', 'to_date' => ''];
?>

<section>
    <div class="f-col f-gap-15" id="dashboard-analytics-root">
        <div class="f-row f-items-center f-justify-between xs-f-col xs-f-items-start xs-f-gap-10">
            <div>
                <h2 class="fs-10 fw-700 m-0-b">Dashboard e Analytics</h2>
                <p class="m-10-t m-0-b">Indicadores em tempo real com dados reais do SQLite.</p>
            </div>
            <span class="badge alert-info">
                <span class="iccon-user-1"></span>
                <?= htmlspecialchars((string) (($user['username'] ?? 'admin')), ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>

        <form
            id="dashboard-filters-form"
            class="card p-15-all m-20-b"
            hx-get="/admin/partials/dashboard"
            hx-target="#admin-content"
            hx-swap="innerHTML"
        >
            <div class="f-row f-items-center f-gap-8 m-0-b m-10-b">
                <span class="iccon-settings-1"></span>
                <strong>Filtros básicos</strong>
            </div>

            <div class="row gap-10">
                <div class="c-xs-12 c-md-3">
                    <label class="d-flex f-col f-gap-5" for="dashboard-project-id">
                        <span>Projeto</span>
                        <select id="dashboard-project-id" class="form-control w-100" name="project_id">
                            <option value="0">Todos</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= (int) $project['id'] ?>" <?= ((int) ($filters['project_id'] ?? 0) === (int) $project['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $project['name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="c-xs-12 c-md-3">
                    <label class="d-flex f-col f-gap-5" for="dashboard-survey-id">
                        <span>Pesquisa</span>
                        <select id="dashboard-survey-id" class="form-control w-100" name="survey_id">
                            <option value="">Todos</option>
                            <?php foreach ($surveyOptions as $surveyOption): ?>
                                <option value="<?= (int) $surveyOption['id'] ?>" <?= ((int) ($filters['survey_id'] ?? 0) === (int) $surveyOption['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $surveyOption['project_name'] . ' - ' . $surveyOption['name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="c-xs-12 c-md-3">
                    <label class="d-flex f-col f-gap-5" for="dashboard-from-date">
                        <span>Data inicial</span>
                        <input id="dashboard-from-date" class="form-control w-100" type="date" name="from_date" value="<?= htmlspecialchars((string) ($filters['from_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </label>
                </div>

                <div class="c-xs-12 c-md-3">
                    <label class="d-flex f-col f-gap-5" for="dashboard-to-date">
                        <span>Data final</span>
                        <input id="dashboard-to-date" class="form-control w-100" type="date" name="to_date" value="<?= htmlspecialchars((string) ($filters['to_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </label>
                </div>
            </div>

            <div class="f-row f-gap-10 m-10-t">
                <button class="btn alert-info" type="submit">
                    <span class="iccon-search-1"></span>
                    Aplicar filtros
                </button>
                <button
                    id="dashboard-clear-filters"
                    class="btn alert-warning"
                    type="button"
                >
                    <span class="iccon-undo-1"></span>
                    Limpar
                </button>
            </div>
        </form>

        <div class="row gap-15 m-20-b">
            <article class="c-xs-12 c-sm-6 c-lg-4">
                <div class="card p-15-all">
                    <p class="fs-4 fw-700 text-uppercase ls-1 m-0-b">
                        <span class="iccon-chart-1"></span>
                        Total de respostas
                    </p>
                    <p class="fs-12 fw-700 m-10-t m-0-b" data-counter-value="<?= (float) ($metrics['total_submissions'] ?? 0) ?>" data-counter-decimals="0">
                        <?= (int) ($metrics['total_submissions'] ?? 0) ?>
                    </p>
                </div>
            </article>

            <article class="c-xs-12 c-sm-6 c-lg-4">
                <div class="card p-15-all">
                    <p class="fs-4 fw-700 text-uppercase ls-1 m-0-b">
                        <span class="iccon-star-1"></span>
                        Média NPS
                    </p>
                    <p class="fs-12 fw-700 m-10-t m-0-b" data-counter-value="<?= (float) (($metrics['avg_nps'] ?? 0.0)) ?>" data-counter-decimals="2">
                        <?= $metrics['avg_nps'] === null ? '0.00' : number_format((float) $metrics['avg_nps'], 2, '.', '') ?>
                    </p>
                </div>
            </article>

            <article class="c-xs-12 c-sm-6 c-lg-4">
                <div class="card p-15-all">
                    <p class="fs-4 fw-700 text-uppercase ls-1 m-0-b">
                        <span class="iccon-check-1"></span>
                        Taxa de conclusão
                    </p>
                    <p class="fs-12 fw-700 m-10-t m-0-b" data-counter-value="<?= (float) ($metrics['completion_rate'] ?? 0.0) ?>" data-counter-decimals="2">
                        <?= number_format((float) ($metrics['completion_rate'] ?? 0), 2, '.', '') ?>%
                    </p>
                    <small class="m-5-t" style="display:block; opacity:.75;">
                        <?= (int) ($metrics['completed_submissions'] ?? 0) ?> concluídas de <?= (int) ($metrics['total_submissions'] ?? 0) ?>
                    </small>
                </div>
            </article>
        </div>

        <div class="card p-15-all">
            <div class="f-row f-items-center f-gap-8 m-0-b m-10-b">
                <span class="iccon-clock-1"></span>
                <h3 class="fs-8 fw-700 m-0-b">Respostas recentes</h3>
            </div>

            <div class="table-responsive">
                <table class="table w-100">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Projeto</th>
                        <th>Pesquisa</th>
                        <th>Gatilho</th>
                        <th>Score NPS</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($recentSubmissions)): ?>
                        <tr>
                            <td colspan="7">Nenhuma resposta encontrada para os filtros atuais.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentSubmissions as $submission): ?>
                            <tr>
                                <td>#<?= (int) $submission['id'] ?></td>
                                <td><?= htmlspecialchars((string) ($submission['project_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($submission['survey_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($submission['trigger_event'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= $submission['score_nps'] === null ? '-' : (int) $submission['score_nps'] ?></td>
                                <td>
                                    <?= ((int) ($submission['is_completed'] ?? 0) === 1) ? 'Concluída' : 'Incompleta' ?>
                                </td>
                                <td><?= htmlspecialchars((string) ($submission['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var root = document.getElementById('dashboard-analytics-root');
    if (!root) {
        return;
    }

    var filtersForm = document.getElementById('dashboard-filters-form');
    var clearBtn = document.getElementById('dashboard-clear-filters');

    if (filtersForm && clearBtn) {
        clearBtn.addEventListener('click', function () {
            filtersForm.reset();

            if (window.htmx) {
                window.htmx.ajax('GET', '/admin/partials/dashboard', {
                    target: '#admin-content',
                    swap: 'innerHTML'
                });
                return;
            }

            window.location.href = '/admin/partials/dashboard';
        });
    }

    root.querySelectorAll('[data-counter-value]').forEach(function (target) {
        var rawValue = parseFloat(target.getAttribute('data-counter-value') || '0');
        var decimals = parseInt(target.getAttribute('data-counter-decimals') || '0', 10);
        var suffix = target.textContent.trim().endsWith('%') ? '%' : '';

        if (window.counterUp) {
            counterUp(target, {
                duration: 800,
                delay: 16,
                formatter: function (value) {
                    var safeValue = Number(value || 0);
                    return safeValue.toFixed(decimals) + suffix;
                }
            });
            return;
        }

        target.textContent = rawValue.toFixed(decimals) + suffix;
    });
})();
</script>
