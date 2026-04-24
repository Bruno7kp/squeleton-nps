<?php
$triggerOptions = $triggerOptions ?? [];
$selectedTrigger = $selectedTrigger ?? ($triggerOptions[0] ?? 'none');
$defaultTrigger = $selectedTrigger;
$checkboxChecked = $defaultTrigger !== 'none' ? 'checked' : '';
$checkboxDisabled = $defaultTrigger === 'none' ? 'disabled' : '';
$snippetAutoOpen = $defaultTrigger === 'none' ? 'false' : 'true';
$snippetShowButton = $defaultTrigger === 'none' ? 'false' : 'true';
$defaultSnippet = '<script src="https://seu-dominio.com/widget-loader.js" data-nps-key="' . htmlspecialchars((string) $project['public_key'], ENT_QUOTES, 'UTF-8') . '" data-nps-trigger="' . htmlspecialchars($defaultTrigger, ENT_QUOTES, 'UTF-8') . '" data-nps-auto-open="' . $snippetAutoOpen . '" data-nps-show-float-button="' . $snippetShowButton . '" defer></script>';
?>
<div class="widget-embed-config"
     data-public-key="<?= htmlspecialchars((string) $project['public_key'], ENT_QUOTES, 'UTF-8') ?>"
     data-nps-trigger="<?= htmlspecialchars($defaultTrigger, ENT_QUOTES, 'UTF-8') ?>"
     data-nps-auto-open="<?= $snippetAutoOpen ?>"
     data-nps-show-float-button="<?= $snippetShowButton ?>">

    <h3 class="fs-8 fw-700 m-0-b">Snippet de Embed</h3>
    <p class="m-10-t m-15-b">
        Projeto: <strong><?= htmlspecialchars((string) $project['name'], ENT_QUOTES, 'UTF-8') ?></strong>
    </p>

    <div class="row gap-15 m-10-b">
        <div class="c-xs-12 c-md-5">
            <label class="d-flex f-col f-gap-5" for="widget-trigger">
                <span class="fw-600">Trigger do widget</span>
                <select id="widget-trigger" name="widget-trigger" class="form-control w-100">
                    <option value="none" <?= $selectedTrigger === 'none' ? 'selected' : '' ?>>Nenhum</option>
                    <?php foreach ($triggerOptions as $trigger): ?>
                        <option value="<?= htmlspecialchars((string) $trigger, ENT_QUOTES, 'UTF-8') ?>"
                            <?= $selectedTrigger === $trigger ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) $trigger, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div class="c-xs-12 c-md-7 d-flex f-col f-justify-end f-gap-10">
            <label class="f-row f-items-center f-gap-10" for="widget-autoload">
                <input id="widget-autoload" name="widget-autoload" type="checkbox" <?= $checkboxChecked ?> <?= $checkboxDisabled ?>>
                <span>Abrir automaticamente</span>
            </label>
            <label class="f-row f-items-center f-gap-10" for="widget-show-float-button">
                <input id="widget-show-float-button" name="widget-show-float-button" type="checkbox" <?= $checkboxChecked ?> <?= $checkboxDisabled ?>>
                <span>Mostrar botão flutuante</span>
            </label>
        </div>
    </div>

    <label class="d-flex f-col f-gap-5">
        <span class="fw-600">Código</span>
        <textarea
            id="widget-embed-snippet"
            data-nps-trigger="<?= htmlspecialchars($defaultTrigger, ENT_QUOTES, 'UTF-8') ?>"
            class="form-control w-100"
            rows="4"
            readonly
            onclick="this.select()"
            style="font-family:monospace;font-size:13px;resize:vertical;"
        ><?= htmlspecialchars($defaultSnippet, ENT_QUOTES, 'UTF-8') ?></textarea>
    </label>

    <p class="fs-6 m-10-t m-0-b">
        Clique no campo acima para selecionar tudo. Use <code>data-nps-auto-open="false"</code> para desativar a abertura automática e <code>data-nps-show-float-button="false"</code> para ocultar o botão flutuante.
    </p>
</div>
