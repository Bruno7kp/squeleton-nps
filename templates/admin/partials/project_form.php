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

    <?php if (!empty($project['public_key'])): ?>
        <?php $triggerOptions = $triggerOptions ?? []; ?>
        <?php $selectedTrigger = $selectedTrigger ?? ($triggerOptions[0] ?? 'none'); ?>
        <?php $defaultTrigger = $selectedTrigger; ?>
        <?php $checkboxChecked = $defaultTrigger !== 'none' ? 'checked' : ''; ?>
        <?php $checkboxDisabled = $defaultTrigger === 'none' ? 'disabled' : ''; ?>
        <?php $snippetAutoOpen = $defaultTrigger === 'none' ? 'false' : 'true'; ?>
        <?php $snippetShowButton = $defaultTrigger === 'none' ? 'false' : 'true'; ?>
        <?php $defaultSnippet = '<script src="https://seu-dominio.com/widget-loader.js" data-nps-key="' . htmlspecialchars((string) $project['public_key'], ENT_QUOTES, 'UTF-8') . '" data-nps-trigger="' . htmlspecialchars($defaultTrigger, ENT_QUOTES, 'UTF-8') . '" data-nps-auto-open="' . $snippetAutoOpen . '" data-nps-show-float-button="' . $snippetShowButton . '" defer></script>'; ?>
        <div class="m-20-t p-15-all widget-embed-config" style="border:1px solid #d8dee8; border-radius:10px;" data-public-key="<?= htmlspecialchars((string) $project['public_key'], ENT_QUOTES, 'UTF-8') ?>" data-nps-trigger="<?= htmlspecialchars($defaultTrigger, ENT_QUOTES, 'UTF-8') ?>" data-nps-auto-open="<?= $snippetAutoOpen ?>" data-nps-show-float-button="<?= $snippetShowButton ?>">
            <p class="fw-700 m-0-b">Snippet de Embed</p>
            <p class="m-10-t m-10-b">Use esta chave para carregar o widget do projeto e escolha o comportamento do script.</p>

            <div class="row gap-15 m-10-b">
                <div class="c-xs-12 c-md-4">
                    <label class="d-flex f-col f-gap-5" for="widget-trigger">
                        <span class="fw-600">Trigger do widget</span>
                        <select id="widget-trigger" name="widget-trigger" class="form-control w-100">
                            <option value="none" <?= $selectedTrigger === 'none' ? 'selected' : '' ?>>Nenhum</option>
                            <?php foreach ($triggerOptions as $trigger): ?>
                                <option value="<?= htmlspecialchars((string) $trigger, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedTrigger === $trigger ? 'selected' : '' ?>><?= htmlspecialchars((string) $trigger, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            </div>

            <div class="row gap-15 m-10-b">
                <div class="c-xs-12">
                    <label class="f-row f-items-center f-gap-10" for="widget-autoload">
                        <input id="widget-autoload" name="widget-autoload" type="checkbox" <?= $checkboxChecked ?> <?= $checkboxDisabled ?> >
                        <span>Abrir automaticamente</span>
                    </label>
                </div>
                <div class="c-xs-12">
                    <label class="f-row f-items-center f-gap-10" for="widget-show-float-button">
                        <input id="widget-show-float-button" name="widget-show-float-button" type="checkbox" <?= $checkboxChecked ?> <?= $checkboxDisabled ?> >
                        <span>Mostrar botão flutuante</span>
                    </label>
                </div>
            </div>

            <pre class="m-0-b" style="white-space: pre-wrap;"><code id="widget-embed-snippet" data-nps-trigger="<?= htmlspecialchars($defaultTrigger, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($defaultSnippet, ENT_QUOTES, 'UTF-8') ?></code></pre>
            <p class="fs-6 m-10-t m-0-b">Use <code>data-nps-auto-open="false"</code> para desativar a abertura automática e <code>data-nps-show-float-button="false"</code> para ocultar o botão flutuante.</p>
        </div>
    <?php endif; ?>
</div>
