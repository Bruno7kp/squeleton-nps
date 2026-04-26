<section>
    <div class="f-col f-gap-20">
        <div class="f-row f-items-center f-justify-between xs-f-col xs-f-items-start">
            <div>
                <h2 class="fs-10 fw-700 m-0-b">Pesquisas</h2>
                <?php if (!empty($filterProject)): ?>
                    <p class="m-10-t m-0-b">
                        Projeto: <strong><?= htmlspecialchars((string) $filterProject['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                        &mdash;
                        <button class="btn" style="background:none;border:none;padding:0;font-size:inherit;cursor:pointer;text-decoration:underline;"
                            hx-get="/admin/partials/surveys"
                            hx-target="#admin-content"
                            hx-swap="innerHTML">Ver todas</button>
                    </p>
                <?php else: ?>
                    <p class="m-10-t m-0-b">Pesquisas por projeto com gatilhos dinamicos e status.</p>
                <?php endif; ?>
            </div>
            <button
                class="btn alert-success xs-m-15-t"
                data-modal-show="survey-form-modal"
                hx-get="/admin/surveys/form"
                hx-target="#survey-modal-body"
                hx-swap="innerHTML"
            >
                Nova Pesquisa
            </button>
        </div>

        <div id="survey-feedback" class="m-10-b">
            <?php if (!empty($errorMessage ?? '')): ?>
                <div class="alert alert-danger p-15-all f-row f-items-center f-justify-between f-gap-10" role="alert">
                    <span><?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8') ?></span>
                    <button type="button" class="btn alert-danger" data-close-alert="survey-feedback">Fechar</button>
                </div>
            <?php endif; ?>
        </div>

        <div id="survey-success-messages" style="display:none;">
            <?= htmlspecialchars((string) json_encode($flashMessages ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="f-col">
            <div class="card p-15-all m-20-b">
                <div class="table-responsive">
                    <table class="table w-100">
                        <thead>
                            <tr>
                                <th>Projeto</th>
                                <th>Nome</th>
                                <th>Gatilho</th>
                                <th>Status</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($surveys)): ?>
                                <tr>
                                    <td colspan="5">Nenhuma pesquisa cadastrada.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($surveys as $survey): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) $survey['project_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) $survey['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) (($survey['trigger_keys'] ?? '') !== '' ? $survey['trigger_keys'] : '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) $survey['status'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-right">
                                            <button
                                                class="btn alert-info"
                                                hx-get="/admin/partials/questions?survey_id=<?= (int) $survey['id'] ?>"
                                                hx-target="#admin-content"
                                                hx-swap="innerHTML"
                                            >
                                                Perguntas
                                            </button>
                                            <button
                                                class="btn alert-warning"
                                                data-modal-show="survey-form-modal"
                                                hx-get="/admin/surveys/form/<?= (int) $survey['id'] ?>"
                                                hx-target="#survey-modal-body"
                                                hx-swap="innerHTML"
                                            >
                                                Editar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var closeButton = document.querySelector('[data-close-alert="survey-feedback"]');
    if (closeButton) {
        closeButton.addEventListener('click', function () {
            var wrapper = document.getElementById('survey-feedback');
            if (wrapper) {
                wrapper.innerHTML = '';
            }
        });
    }

    var el = document.getElementById('survey-success-messages');
    if (!el || typeof Toastify !== 'function') {
        return;
    }

    var payload = [];
    try {
        payload = JSON.parse(el.textContent || '[]');
    } catch (error) {
        payload = [];
    }

    payload.forEach(function (message) {
        if (message.type !== 'success') {
            return;
        }

        Toastify({
            text: message.message,
            duration: 2800,
            close: true,
            gravity: 'top',
            position: 'right',
            style: {
                background: 'linear-gradient(to right, #00b09b, #96c93d)'
            }
        }).showToast();
    });
})();
</script>
