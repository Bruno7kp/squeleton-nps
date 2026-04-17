<section>
    <div class="f-col f-gap-20">
        <div class="f-row f-items-center f-justify-between xs-f-col xs-f-items-start">
            <div>
                <h2 class="fs-10 fw-700 m-0-b">Perguntas e Regras</h2>
                <p class="m-10-t m-0-b">
                    Pesquisa: <strong><?= htmlspecialchars((string) ($survey['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    (<?= htmlspecialchars((string) ($survey['trigger_event'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)
                </p>
            </div>
            <div class="f-row f-gap-10 xs-m-15-t">
                <button
                    class="btn alert-success"
                    hx-get="/admin/questions/form?survey_id=<?= (int) ($survey['id'] ?? 0) ?>"
                    hx-target="#question-form-panel"
                    hx-swap="innerHTML"
                >
                    Nova Pergunta
                </button>
                <button
                    class="btn alert-info"
                    hx-get="/admin/partials/surveys"
                    hx-target="#admin-content"
                    hx-swap="innerHTML"
                >
                    Voltar para Pesquisas
                </button>
            </div>
        </div>

        <div id="question-feedback">
            <?php if (!empty($errorMessage ?? '')): ?>
                <div class="alert alert-danger p-15-all" role="alert">
                    <?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
        </div>

        <div id="question-success-messages" style="display:none;">
            <?= htmlspecialchars((string) json_encode($flashMessages ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="row gap-20">
            <div class="c-xs-12 c-lg-7">
                <div class="card p-15-all m-20-b">
                    <h3 class="fs-8 fw-700 m-0-b m-10-b">Perguntas</h3>
                    <div class="table-responsive">
                        <table class="table w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Campo</th>
                                    <th>Tipo</th>
                                    <th>Obrig.</th>
                                    <th class="text-right">Acoes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($questions)): ?>
                                    <tr>
                                        <td colspan="5">Nenhuma pergunta cadastrada.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($questions as $question): ?>
                                        <tr>
                                            <td><?= (int) $question['position'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars((string) $question['field_name'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                                <small><?= htmlspecialchars((string) $question['label'], ENT_QUOTES, 'UTF-8') ?></small>
                                            </td>
                                            <td><?= htmlspecialchars((string) $question['question_type'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= ((int) $question['is_required'] === 1) ? 'Sim' : 'Nao' ?></td>
                                            <td class="text-right">
                                                <div class="f-row f-gap-5 f-justify-end">
                                                    <form hx-post="/admin/questions/<?= (int) $question['id'] ?>/move" hx-target="#admin-content" hx-swap="innerHTML">
                                                        <?= \App\Support\Csrf::hiddenInput() ?>
                                                        <input type="hidden" name="direction" value="up">
                                                        <button class="btn alert-info" type="submit">Up</button>
                                                    </form>
                                                    <form hx-post="/admin/questions/<?= (int) $question['id'] ?>/move" hx-target="#admin-content" hx-swap="innerHTML">
                                                        <?= \App\Support\Csrf::hiddenInput() ?>
                                                        <input type="hidden" name="direction" value="down">
                                                        <button class="btn alert-info" type="submit">Down</button>
                                                    </form>
                                                    <button class="btn alert-info" hx-get="/admin/questions/form/<?= (int) $question['id'] ?>" hx-target="#question-form-panel" hx-swap="innerHTML">Editar</button>
                                                    <form hx-post="/admin/questions/<?= (int) $question['id'] ?>/delete" hx-target="#admin-content" hx-swap="innerHTML">
                                                        <?= \App\Support\Csrf::hiddenInput() ?>
                                                        <button class="btn alert-danger" type="submit">Excluir</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card p-15-all">
                    <h3 class="fs-8 fw-700 m-0-b m-10-b">Regras Condicionais</h3>
                    <form class="f-col f-gap-10 m-10-b" hx-post="/admin/rules" hx-target="#admin-content" hx-swap="innerHTML">
                        <?= \App\Support\Csrf::hiddenInput() ?>
                        <input type="hidden" name="survey_id" value="<?= (int) ($survey['id'] ?? 0) ?>">

                        <label class="f-col f-gap-5" for="rule-source-question">
                            <span class="fw-600">Quando pergunta</span>
                            <select id="rule-source-question" class="form-control" name="source_question_id" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($questions as $question): ?>
                                    <option value="<?= (int) $question['id'] ?>"><?= htmlspecialchars((string) $question['field_name'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars((string) $question['label'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <div class="row gap-10">
                            <div class="c-xs-12 c-md-4">
                                <label class="f-col f-gap-5" for="rule-operator">
                                    <span class="fw-600">Operador</span>
                                    <select id="rule-operator" class="form-control" name="operator" required>
                                        <?php foreach ($ruleOperatorOptions as $operator): ?>
                                            <option value="<?= htmlspecialchars((string) $operator, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $operator, ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            </div>
                            <div class="c-xs-12 c-md-4">
                                <label class="f-col f-gap-5" for="rule-compare-value">
                                    <span class="fw-600">Valor</span>
                                    <input id="rule-compare-value" class="form-control" type="text" name="compare_value" required>
                                </label>
                            </div>
                            <div class="c-xs-12 c-md-4">
                                <label class="f-col f-gap-5" for="rule-action">
                                    <span class="fw-600">Acao</span>
                                    <select id="rule-action" class="form-control" name="action" required>
                                        <option value="show">show</option>
                                    </select>
                                </label>
                            </div>
                        </div>

                        <label class="f-col f-gap-5" for="rule-target-question">
                            <span class="fw-600">Mostrar pergunta</span>
                            <select id="rule-target-question" class="form-control" name="target_question_id" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($questions as $question): ?>
                                    <option value="<?= (int) $question['id'] ?>"><?= htmlspecialchars((string) $question['field_name'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars((string) $question['label'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <button class="btn alert-info" type="submit">Adicionar Regra</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Condicao</th>
                                    <th>Acao</th>
                                    <th class="text-right">Acoes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rules)): ?>
                                    <tr>
                                        <td colspan="4">Nenhuma regra cadastrada.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($rules as $rule): ?>
                                        <tr>
                                            <td><?= (int) $rule['position'] ?></td>
                                            <td>
                                                Se <strong><?= htmlspecialchars((string) $rule['source_label'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                <code><?= htmlspecialchars((string) $rule['operator'], ENT_QUOTES, 'UTF-8') ?></code>
                                                <strong><?= htmlspecialchars((string) $rule['compare_value'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars((string) $rule['action'], ENT_QUOTES, 'UTF-8') ?>
                                                <?= htmlspecialchars((string) $rule['target_label'], ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td class="text-right">
                                                <form hx-post="/admin/rules/<?= (int) $rule['id'] ?>/delete" hx-target="#admin-content" hx-swap="innerHTML">
                                                    <?= \App\Support\Csrf::hiddenInput() ?>
                                                    <input type="hidden" name="survey_id" value="<?= (int) ($survey['id'] ?? 0) ?>">
                                                    <button class="btn alert-danger" type="submit">Excluir</button>
                                                </form>
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
                <div id="question-form-panel" class="card p-20-all">
                    <h3 class="fs-8 fw-700 m-0-b">Nova Pergunta</h3>
                    <p class="m-10-t m-0-b">Clique em "Nova Pergunta" para abrir o formulario.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var el = document.getElementById('question-success-messages');
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
            duration: 2600,
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
