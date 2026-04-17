<section>
    <div class="f-col f-gap-20">
        <div class="f-row f-items-center f-justify-between xs-f-col xs-f-items-start">
            <div>
                <h2 class="fs-10 fw-700 m-0-b">Pesquisas</h2>
                <p class="m-10-t m-0-b">CRUD de pesquisas por projeto com gatilho e status.</p>
            </div>
            <button
                class="btn alert-success xs-m-15-t"
                hx-get="/admin/surveys/form"
                hx-target="#survey-form-panel"
                hx-swap="innerHTML"
            >
                Nova Pesquisa
            </button>
        </div>

        <div id="survey-feedback">
            <?php if (!empty($errorMessage ?? '')): ?>
                <div class="alert alert-danger p-15-all" role="alert">
                    <?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
        </div>

        <div id="survey-success-messages" style="display:none;">
            <?= htmlspecialchars((string) json_encode($flashMessages ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="row gap-20">
            <div class="c-xs-12 c-lg-7">
                <div class="card p-15-all">
                    <div class="table-responsive">
                        <table class="table w-100">
                            <thead>
                                <tr>
                                    <th>Projeto</th>
                                    <th>Nome</th>
                                    <th>Gatilho</th>
                                    <th>Status</th>
                                    <th class="text-right">Acoes</th>
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
                                            <td><?= htmlspecialchars((string) $survey['trigger_event'], ENT_QUOTES, 'UTF-8') ?></td>
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
                                                    class="btn alert-info"
                                                    hx-get="/admin/surveys/form/<?= (int) $survey['id'] ?>"
                                                    hx-target="#survey-form-panel"
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

            <div class="c-xs-12 c-lg-5">
                <div id="survey-form-panel" class="card p-20-all">
                    <h3 class="fs-8 fw-700 m-0-b">Criar Pesquisa</h3>
                    <p class="m-10-t m-0-b">Clique em "Nova Pesquisa" para abrir o formulario.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
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
