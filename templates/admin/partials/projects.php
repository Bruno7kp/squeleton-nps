<section>
    <div class="f-col f-gap-20">
        <div class="f-row f-items-center f-justify-between xs-f-col xs-f-items-start">
            <div>
                <h2 class="fs-10 fw-700 m-0-b">Projetos</h2>
                <p class="m-10-t m-0-b">Gerencie projetos e chaves de integração do widget.</p>
            </div>
            <button
                class="btn alert-success xs-m-15-t"
                data-modal-show="project-form-modal"
                hx-get="/admin/projects/form"
                hx-target="#project-modal-body"
                hx-swap="innerHTML"
            >
                Novo Projeto
            </button>
        </div>

        <div id="project-feedback" class="m-10-b">
            <?php if (!empty($errorMessage ?? '')): ?>
                <div class="alert alert-danger p-15-all" role="alert">
                    <?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
        </div>

        <div id="project-success-messages" style="display:none;">
            <?= htmlspecialchars((string) json_encode($flashMessages ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="f-col">
            <div class="card p-15-all m-20-b">
                <div class="table-responsive">
                    <table class="table w-100">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Chave</th>
                                <th>Status</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($projects)): ?>
                                <tr>
                                    <td colspan="5">Nenhum projeto cadastrado.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($projects as $project): ?>
                            <tr>
                                        <td><?= htmlspecialchars((string) $project['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><code><?= htmlspecialchars((string) $project['public_key'], ENT_QUOTES, 'UTF-8') ?></code></td>
                                        <td><?= ((int) $project['is_active'] === 1) ? 'Ativo' : 'Inativo' ?></td>
                                        <td class="text-right">
                                            <button
                                                class="btn alert-info"
                                                hx-get="/admin/partials/surveys?project_id=<?= (int) $project['id'] ?>"
                                                hx-target="#admin-content"
                                                hx-swap="innerHTML"
                                            >
                                                Pesquisas
                                            </button>
                                            <button
                                                class="btn alert-info"
                                                data-modal-show="snippet-modal"
                                                hx-get="/admin/projects/snippet/<?= (int) $project['id'] ?>"
                                                hx-target="#snippet-modal-body"
                                                hx-swap="innerHTML"
                                            >
                                                Snippet
                                            </button>
                                            <button
                                                class="btn alert-info"
                                                data-modal-show="project-form-modal"
                                                hx-get="/admin/projects/form/<?= (int) $project['id'] ?>"
                                                hx-target="#project-modal-body"
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
    var el = document.getElementById('project-success-messages');
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
