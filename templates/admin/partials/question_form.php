<div>
    <h3 class="fs-8 fw-700 m-0-b"><?= !empty($question['id']) ? 'Editar Pergunta' : 'Nova Pergunta' ?></h3>
    <p class="m-10-t m-0-b">
        Pesquisa: <?= htmlspecialchars((string) ($survey['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
    </p>

    <?php if (!empty($errorMessage ?? '')): ?>
        <div class="alert alert-danger p-15-all m-15-t" role="alert">
            <?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form
        class="f-col f-gap-15 m-20-t"
        hx-post="<?= !empty($question['id']) ? '/admin/questions/' . (int) $question['id'] : '/admin/questions' ?>"
        hx-target="#admin-content"
        hx-swap="innerHTML"
    >
        <?= \App\Support\Csrf::hiddenInput() ?>
        <input type="hidden" name="survey_id" value="<?= (int) ($survey['id'] ?? 0) ?>">

        <label class="f-col f-gap-5" for="question-label">
            <span class="fw-600">Label</span>
            <input id="question-label" class="form-control" type="text" name="label" value="<?= htmlspecialchars((string) ($question['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </label>

        <label class="f-col f-gap-5" for="question-field-name">
            <span class="fw-600">Campo tecnico (field_name)</span>
            <input id="question-field-name" class="form-control" type="text" name="field_name" value="<?= htmlspecialchars((string) ($question['field_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="nps_score" required>
        </label>

        <label class="f-col f-gap-5" for="question-type">
            <span class="fw-600">Tipo</span>
            <select id="question-type" class="form-control" name="question_type" required>
                <?php foreach ($questionTypeOptions as $type): ?>
                    <option value="<?= htmlspecialchars((string) $type, ENT_QUOTES, 'UTF-8') ?>" <?= (($question['question_type'] ?? '') === $type) ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $type, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="f-row f-items-center f-gap-10" for="question-required">
            <input id="question-required" type="checkbox" name="is_required" value="1" <?= ((int) ($question['is_required'] ?? 0) === 1) ? 'checked' : '' ?>>
            <span>Pergunta obrigatoria</span>
        </label>

        <label class="f-col f-gap-5" for="question-placeholder">
            <span class="fw-600">Placeholder</span>
            <input id="question-placeholder" class="form-control" type="text" name="placeholder" value="<?= htmlspecialchars((string) ($question['placeholder'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="f-col f-gap-5" for="question-help-text">
            <span class="fw-600">Texto de ajuda</span>
            <textarea id="question-help-text" class="form-control" name="help_text" rows="2"><?= htmlspecialchars((string) ($question['help_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </label>

        <label class="f-col f-gap-5" for="question-options">
            <span class="fw-600">Opcoes (uma por linha para select/checkbox/radio)</span>
            <textarea id="question-options" class="form-control" name="options_text" rows="4"><?= htmlspecialchars((string) implode(PHP_EOL, $question['options'] ?? []), ENT_QUOTES, 'UTF-8') ?></textarea>
        </label>

        <button class="btn alert-info" type="submit">Salvar Pergunta</button>
    </form>
</div>
