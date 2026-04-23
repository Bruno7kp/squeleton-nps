<div>
    <h3 class="fs-8 fw-700 m-0-b"><?= !empty($survey['id']) ? 'Editar Pesquisa' : 'Nova Pesquisa' ?></h3>
    <p class="m-10-t m-0-b">Defina projeto, gatilhos dinamicos e status da pesquisa.</p>

    <?php
    $triggerKeys = $survey['trigger_keys'] ?? [];
    if (!is_array($triggerKeys)) {
        $triggerKeys = [];
    }
    $triggerText = implode("\n", array_map(static fn (mixed $item): string => (string) $item, $triggerKeys));
    ?>

    <?php if (!empty($errorMessage ?? '')): ?>
        <div class="alert alert-danger p-15-all m-15-t" role="alert">
            <?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form
        class="f-col f-gap-15 m-20-t"
        hx-post="<?= !empty($survey['id']) ? '/admin/surveys/' . (int) $survey['id'] : '/admin/surveys' ?>"
        hx-target="#admin-content"
        hx-swap="innerHTML"
    >
        <?= \App\Support\Csrf::hiddenInput() ?>
        <label class="f-col f-gap-5" for="survey-project-id">
            <span class="fw-600">Projeto</span>
            <select id="survey-project-id" class="form-control" name="project_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($projects as $project): ?>
                    <option value="<?= (int) $project['id'] ?>" <?= ((int) ($survey['project_id'] ?? 0) === (int) $project['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $project['name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="f-col f-gap-5" for="survey-name">
            <span class="fw-600">Nome interno</span>
            <input id="survey-name" class="form-control" type="text" name="name" value="<?= htmlspecialchars((string) ($survey['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </label>

        <label class="f-col f-gap-5" for="survey-slug">
            <span class="fw-600">Slug</span>
            <input id="survey-slug" class="form-control" type="text" name="slug" value="<?= htmlspecialchars((string) ($survey['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="nps-on-load" required>
        </label>

        <label class="f-col f-gap-5" for="survey-trigger-keys">
            <span class="fw-600">Gatilhos</span>
            <textarea id="survey-trigger-keys" class="form-control" name="trigger_keys" rows="4" placeholder="one_trigger_per_line\nanother_trigger\ncompleted_half_course" required><?= htmlspecialchars($triggerText, ENT_QUOTES, 'UTF-8') ?></textarea>
            <small>Use um gatilho por linha. Cada gatilho pode ser usado por apenas uma pesquisa no mesmo projeto.</small>
        </label>

        <label class="f-col f-gap-5" for="survey-status">
            <span class="fw-600">Status</span>
            <select id="survey-status" class="form-control" name="status" required>
                <?php foreach ($statusOptions as $status): ?>
                    <option value="<?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>" <?= (($survey['status'] ?? '') === $status) ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="f-col f-gap-5" for="survey-title">
            <span class="fw-600">Título exibido</span>
            <input id="survey-title" class="form-control" type="text" name="title" value="<?= htmlspecialchars((string) ($survey['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="f-col f-gap-5" for="survey-description">
            <span class="fw-600">Descrição</span>
            <textarea id="survey-description" class="form-control" name="description" rows="3"><?= htmlspecialchars((string) ($survey['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </label>

        <button class="btn alert-info" type="submit">Salvar Pesquisa</button>
    </form>
</div>

<?php if (empty($survey['id'])): ?>
<script>
(function () {
    var nameInput = document.getElementById('survey-name');
    var slugInput = document.getElementById('survey-slug');

    if (!nameInput || !slugInput) {
        return;
    }

    var slugEditedManually = false;

    function slugify(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .replace(/-{2,}/g, '-');
    }

    nameInput.addEventListener('input', function () {
        if (slugEditedManually) {
            return;
        }

        slugInput.value = slugify(nameInput.value);
    });

    slugInput.addEventListener('input', function () {
        if (slugInput.value.trim() !== '') {
            slugEditedManually = true;
        }
    });
})();
</script>
<?php endif; ?>
