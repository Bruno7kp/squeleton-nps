<div>
    <h3 class="fs-8 fw-700 m-0-b"><?= !empty($project['id']) ? 'Editar Projeto' : 'Novo Projeto' ?></h3>
    <p class="m-10-t m-0-b">Configure os dados e reutilize a chave no widget embed.</p>

    <?php if (!empty($errorMessage ?? '')): ?>
        <div class="alert alert-danger p-15-all m-15-t" role="alert">
            <?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form
        class="f-col f-gap-15 m-20-t"
        hx-post="<?= !empty($project['id']) ? '/admin/projects/' . (int) $project['id'] : '/admin/projects' ?>"
        hx-target="#admin-content"
        hx-swap="innerHTML"
    >
        <?= \App\Support\Csrf::hiddenInput() ?>
        <label class="f-col f-gap-5" for="project-name">
            <span class="fw-600">Nome</span>
            <input id="project-name" class="form-control" type="text" name="name" value="<?= htmlspecialchars((string) ($project['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </label>

        <label class="f-col f-gap-5" for="project-slug">
            <span class="fw-600">Slug</span>
            <input id="project-slug" class="form-control" type="text" name="slug" value="<?= htmlspecialchars((string) ($project['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="meu-projeto-nps" required>
        </label>

        <label class="f-col f-gap-5" for="project-description">
            <span class="fw-600">Descrição</span>
            <textarea id="project-description" class="form-control" name="description" rows="3" placeholder="Contexto do projeto para equipe/admin."><?= htmlspecialchars((string) ($project['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </label>

        <label class="f-row f-items-center f-gap-10" for="project-active">
            <input id="project-active" type="checkbox" name="is_active" value="1" <?= ((int) ($project['is_active'] ?? 1) === 1) ? 'checked' : '' ?>>
            <span>Projeto ativo</span>
        </label>

        <button class="btn alert-info" type="submit">Salvar Projeto</button>
    </form>

    <?php if (!empty($project['public_key'])): ?>
        <div class="m-20-t p-15-all" style="border:1px solid #d8dee8; border-radius:10px;">
            <p class="fw-700 m-0-b">Snippet de Embed</p>
            <p class="m-10-t m-10-b">Use esta chave para carregar o widget do projeto:</p>
            <pre class="m-0-b" style="white-space: pre-wrap;"><code>&lt;script src="https://seu-dominio.com/widget-loader.js" data-nps-key="<?= htmlspecialchars((string) $project['public_key'], ENT_QUOTES, 'UTF-8') ?>" data-nps-trigger="on_load" defer&gt;&lt;/script&gt;</code></pre>
        </div>
    <?php endif; ?>
</div>
