<div>
    <h3 class="fs-8 fw-700 m-0-b"><?= !empty($project['id']) ? 'Editar Projeto' : 'Novo Projeto' ?></h3>
    <p class="m-10-t m-0-b">Configure os dados e reutilize a chave no widget embed.</p>

    <?php if (!empty($errorMessage ?? '')): ?>
        <div class="alert alert-danger p-15-all m-15-t" role="alert">
            <?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form
        class="d-flex f-col f-gap-15 m-20-t"
        hx-post="<?= !empty($project['id']) ? '/admin/projects/' . (int) $project['id'] : '/admin/projects' ?>"
        hx-target="#admin-content"
        hx-swap="innerHTML"
    >
        <?= \App\Support\Csrf::hiddenInput() ?>
        <div class="row gap-15">
            <div class="c-xs-12 c-md-6">
                <label class="d-flex f-col f-gap-5" for="project-name">
                    <span class="fw-600">Nome</span>
                    <input id="project-name" class="form-control w-100" type="text" name="name" value="<?= htmlspecialchars((string) ($project['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                </label>
            </div>

            <div class="c-xs-12 c-md-6">
                <label class="d-flex f-col f-gap-5" for="project-slug">
                    <span class="fw-600">Slug</span>
                    <input id="project-slug" class="form-control w-100" type="text" name="slug" value="<?= htmlspecialchars((string) ($project['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="meu-projeto-nps" required>
                </label>
            </div>

            <div class="c-xs-12">
                <label class="d-flex f-col f-gap-5" for="project-description">
                    <span class="fw-600">Descrição</span>
                    <textarea id="project-description" class="form-control w-100" name="description" rows="3" placeholder="Contexto do projeto para equipe/admin."><?= htmlspecialchars((string) ($project['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>
            </div>
        </div>

        <label class="f-row f-items-center f-gap-10" for="project-active">
            <input id="project-active" type="checkbox" name="is_active" value="1" <?= ((int) ($project['is_active'] ?? 1) === 1) ? 'checked' : '' ?>>
            <span>Projeto ativo</span>
        </label>

        <button class="btn alert-info" type="submit">Salvar Projeto</button>
    </form>

    <?php if (empty($project['id'])): ?>
    <script>
    (function () {
        var nameInput = document.getElementById('project-name');
        var slugInput = document.getElementById('project-slug');

        if (!nameInput || !slugInput) {
            return;
        }

        var slugEditedManually = false;

        function slugify(value) {
            return String(value || '')
                .normalize('NFD')
                .replace(/[̀-ͯ]/g, '')
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

</div>
