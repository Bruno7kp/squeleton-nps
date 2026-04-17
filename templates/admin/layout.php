<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | NPS Squeleton.dev</title>
    <link rel="stylesheet" href="https://cdn.squeleton.dev/squeleton.v4.min.css">
    <script src="https://cdn.squeleton.dev/squeleton-main.v4.min.js"></script>
</head>
<body>
<header class="container-fluid p-20-all">
    <div class="container">
        <div class="row f-items-center f-justify-between">
            <div class="c-xs-12 c-md-auto">
                <h1 class="fs-11 fw-700 m-0-b">NPS Admin</h1>
                <p class="fs-5 m-5-t m-0-b">Gestao inicial da plataforma</p>
            </div>
            <div class="c-xs-12 c-md-auto xs-m-15-t">
                <div class="f-row f-items-center f-gap-10 xs-f-col xs-f-items-start">
                    <span class="fs-5 fw-600">Usuario: <?= htmlspecialchars((string) (($user['username'] ?? 'admin')), ENT_QUOTES, 'UTF-8') ?></span>
                    <form method="post" action="/logout">
                        <button class="btn alert-danger" type="submit">Sair</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<main class="container p-20-b">
    <section class="row">
        <div class="c-xs-12 c-md-3">
            <aside class="card p-20-all">
                <h2 class="fs-8 fw-700 m-0-b m-15-b">Menu</h2>
                <div class="f-col f-gap-10">
                    <button
                        class="btn alert-info text-left"
                        hx-get="/admin/partials/dashboard"
                        hx-target="#admin-content"
                        hx-swap="innerHTML"
                    >
                        Dashboard Base
                    </button>
                    <button
                        class="btn alert-info text-left"
                        hx-get="/admin/partials/projects"
                        hx-target="#admin-content"
                        hx-swap="innerHTML"
                    >
                        Projetos
                    </button>
                    <button
                        class="btn alert-info text-left"
                        hx-get="/admin/partials/surveys"
                        hx-target="#admin-content"
                        hx-swap="innerHTML"
                    >
                        Pesquisas
                    </button>
                </div>
            </aside>
        </div>

        <div class="c-xs-12 c-md-9 xs-m-20-t">
            <div class="card p-20-all" id="admin-content" hx-get="/admin/partials/dashboard" hx-trigger="load" hx-swap="innerHTML">
                <p>Carregando painel...</p>
            </div>
        </div>
    </section>
</main>

<script src="https://cdn.squeleton.dev/squeleton-scripts.v4.min.js"></script>
<script>
(function () {
    var messages = <?= json_encode($flashMessages ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

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
