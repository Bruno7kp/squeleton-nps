<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | NPS Squeleton.dev</title>
    <link rel="stylesheet" href="https://cdn.squeleton.dev/squeleton.v4.min.css">
    <link rel="stylesheet" href="/admin.css">
    <script src="https://cdn.squeleton.dev/squeleton-main.v4.min.js"></script>
</head>
<body class="admin-app">
<header class="container-fluid p-20-all m-20-b">
    <div class="container">
        <div class="d-flex f-items-center f-justify-between f-gap-15 xs-f-col md-f-row">
            <div class="d-flex f-items-center f-gap-10 xs-f-col md-f-row xs-f-items-start md-f-items-center">
                <h1 class="fs-11 fw-700 m-0-b">NPS Admin</h1>
                <span class="admin-top-brand-subtitle fs-5">Gestão inicial da plataforma</span>
            </div>
            <div class="w-100 md-w-auto">
                <nav class="d-flex f-items-center f-gap-10 xs-f-col md-f-row md-f-justify-end" aria-label="Menu principal admin">
                    <button
                        class="btn admin-top-nav-btn"
                        hx-get="/admin/partials/dashboard"
                        hx-target="#admin-content"
                        hx-swap="innerHTML"
                    >
                        <span class="iccon-chart-1" aria-hidden="true"></span>
                        <span>Home</span>
                    </button>
                    <button
                        class="btn admin-top-nav-btn"
                        hx-get="/admin/partials/projects"
                        hx-target="#admin-content"
                        hx-swap="innerHTML"
                    >
                        <span class="iccon-settings-1" aria-hidden="true"></span>
                        <span>Projetos</span>
                    </button>
                    <button
                        class="btn admin-top-nav-btn"
                        hx-get="/admin/partials/surveys"
                        hx-target="#admin-content"
                        hx-swap="innerHTML"
                    >
                        <span class="iccon-search-1" aria-hidden="true"></span>
                        <span>Pesquisas</span>
                    </button>
                    <form method="post" action="/logout" class="m-0-b">
                        <?= \App\Support\Csrf::hiddenInput() ?>
                        <button class="btn admin-top-nav-btn admin-top-nav-btn-danger" type="submit">
                            <span class="iccon-undo-1" aria-hidden="true"></span>
                            <span>Sair</span>
                        </button>
                    </form>
                </nav>
            </div>
        </div>
    </div>
</header>

<main class="container p-20-b">
    <div id="admin-loading" class="alert alert-info p-10-all m-0-b m-10-b" style="display:none;">
        Carregando dados...
    </div>

    <section class="row">
        <div class="c-xs-12">
            <div class="card p-20-all" id="admin-content" hx-get="/admin/partials/dashboard" hx-trigger="load" hx-swap="innerHTML">
                <p>Carregando painel...</p>
            </div>
        </div>
    </section>
</main>

<!-- Modal: Projeto -->
<div data-modal="project-form-modal" class="modal-dialog" aria-hidden="true" aria-modal="true" role="dialog" tabindex="-1">
    <div class="dialog-content">
        <div class="dialog-backdrop" data-modal-hide></div>
        <div class="dialog-inline w-700px">
            <button class="dialog-close" data-modal-hide aria-label="Fechar"></button>
            <div class="modal-popup border-rd-10 p-30-all" id="project-modal-body">
                <p>Carregando formulário...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Pesquisa -->
<div data-modal="survey-form-modal" class="modal-dialog" aria-hidden="true" aria-modal="true" role="dialog" tabindex="-1">
    <div class="dialog-content">
        <div class="dialog-backdrop" data-modal-hide></div>
        <div class="dialog-inline w-700px">
            <button class="dialog-close" data-modal-hide aria-label="Fechar"></button>
            <div class="modal-popup border-rd-10 p-30-all" id="survey-modal-body">
                <p>Carregando formulário...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Pergunta -->
<div data-modal="question-form-modal" class="modal-dialog" aria-hidden="true" aria-modal="true" role="dialog" tabindex="-1">
    <div class="dialog-content">
        <div class="dialog-backdrop" data-modal-hide></div>
        <div class="dialog-inline w-600px">
            <button class="dialog-close" data-modal-hide aria-label="Fechar"></button>
            <div class="modal-popup border-rd-10 p-30-all" id="question-modal-body">
                <p>Carregando formulário...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Snippet de Embed -->
<div data-modal="snippet-modal" class="modal-dialog" aria-hidden="true" aria-modal="true" role="dialog" tabindex="-1">
    <div class="dialog-content">
        <div class="dialog-backdrop" data-modal-hide></div>
        <div class="dialog-inline w-600px">
            <button class="dialog-close" data-modal-hide aria-label="Fechar"></button>
            <div class="modal-popup border-rd-10 p-30-all" id="snippet-modal-body">
                <p>Carregando snippet...</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.squeleton.dev/squeleton-scripts.v4.min.js"></script>
<script>
(function () {
    var messages = <?= json_encode($flashMessages ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    var loadingEl = document.getElementById('admin-loading');

    function showLoading() {
        if (!loadingEl) {
            return;
        }
        loadingEl.style.display = 'block';
    }

    function hideLoading() {
        if (!loadingEl) {
            return;
        }
        loadingEl.style.display = 'none';
    }

    document.body.addEventListener('htmx:beforeRequest', showLoading);
    document.body.addEventListener('htmx:afterRequest', hideLoading);
    document.body.addEventListener('htmx:responseError', hideLoading);

    function initWidgetSnippetBuilder(container) {
        var root = container ? container.querySelector('.widget-embed-config') : document.querySelector('.widget-embed-config');
        if (!root || root.dataset.widgetSnippetInitialized === '1') {
            return;
        }

        var publicKey = root.dataset.publicKey || '';
        var defaultAutoOpen = root.dataset.npsAutoOpen !== 'false';
        var defaultShowFloatButton = root.dataset.npsShowFloatButton !== 'false';

        if (!publicKey) {
            return;
        }

        var triggerSelect = root.querySelector('#widget-trigger');
        var autoOpenCheckbox = root.querySelector('#widget-autoload');
        var showButtonCheckbox = root.querySelector('#widget-show-float-button');
        var snippetElement = root.querySelector('#widget-embed-snippet');

        if (!triggerSelect || !autoOpenCheckbox || !showButtonCheckbox || !snippetElement) {
            return;
        }

        root.dataset.widgetSnippetInitialized = '1';

        function updateSnippet() {
            var trigger = triggerSelect.value;
            var shouldDisable = trigger === 'none';

            if (shouldDisable) {
                autoOpenCheckbox.checked = false;
                showButtonCheckbox.checked = false;
            }

            autoOpenCheckbox.disabled = shouldDisable;
            showButtonCheckbox.disabled = shouldDisable;
            snippetElement.dataset.npsTrigger = trigger;
            snippetElement.value = computeSnippet(publicKey, trigger, autoOpenCheckbox.checked, showButtonCheckbox.checked);
        }

        triggerSelect.addEventListener('change', updateSnippet);
        autoOpenCheckbox.addEventListener('change', updateSnippet);
        showButtonCheckbox.addEventListener('change', updateSnippet);
        updateSnippet();
    }

    function computeSnippet(publicKey, trigger, autoOpen, showFloatButton) {
        return '<script src="https://seu-dominio.com/widget-loader.js" data-nps-key="' + publicKey + '" data-nps-trigger="' + trigger + '" data-nps-auto-open="' + (autoOpen ? 'true' : 'false') + '" data-nps-show-float-button="' + (showFloatButton ? 'true' : 'false') + '" defer><\/script>';
    }

    function slugify(value) {
        return String(value)
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function initProjectSlugAutoFill(container) {
        var root = container ? container.querySelector('form') : document.querySelector('form');
        if (!root) {
            return;
        }

        var nameInput = root.querySelector('#project-name');
        var slugInput = root.querySelector('#project-slug');
        if (!nameInput || !slugInput) {
            return;
        }

        var manual = slugInput.value !== '' && slugInput.value !== slugify(nameInput.value);

        function updateSlug() {
            var generated = slugify(nameInput.value);
            if (!manual) {
                slugInput.value = generated;
            }
        }

        nameInput.addEventListener('input', function () {
            updateSlug();
            manual = slugInput.value !== slugify(nameInput.value);
        });

        slugInput.addEventListener('input', function () {
            manual = slugInput.value !== slugify(nameInput.value);
        });

        updateSlug();
    }

    function onHtmxSwap(event) {
        var target = event && event.detail && event.detail.target ? event.detail.target : document.body;
        initWidgetSnippetBuilder(target);
        initProjectSlugAutoFill(target);
    }

    document.body.addEventListener('htmx:afterSwap', onHtmxSwap);
    document.body.addEventListener('htmx:afterOnLoad', onHtmxSwap);
    document.body.addEventListener('htmx:afterSettle', onHtmxSwap);

    // Fechar qualquer modal aberto quando #admin-content trocar de conteúdo.
    // MutationObserver é mais confiável que eventos HTMX para detectar o swap.
    var adminContentEl = document.getElementById('admin-content');
    if (adminContentEl) {
        new MutationObserver(function () {
            document.querySelectorAll('.modal-dialog').forEach(function (modalEl) {
                if (modalEl.getAttribute('aria-hidden') !== 'true') {
                    var closeBtn = modalEl.querySelector('.dialog-close');
                    if (closeBtn) {
                        closeBtn.click();
                    }
                }
            });
        }).observe(adminContentEl, { childList: true });
    }

    var widgetSnippetObserver = new MutationObserver(function () {
        initWidgetSnippetBuilder(document.body);
        initProjectSlugAutoFill(document.body);
    });
    widgetSnippetObserver.observe(document.body, { childList: true, subtree: true });

    initWidgetSnippetBuilder(document.body);
    initProjectSlugAutoFill(document.body);

    messages.forEach(function (message) {
        if (message.type !== 'success' || typeof Toastify !== 'function') {
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
</body>
</html>
