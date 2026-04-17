<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin | NPS Squeleton.dev</title>
    <link rel="stylesheet" href="https://cdn.squeleton.dev/squeleton.v4.min.css">
    <script src="https://cdn.squeleton.dev/squeleton-main.v4.min.js"></script>
</head>
<body>
<main class="container p-40-t p-20-b">
    <section class="row">
        <div class="c-xs-12 c-sm-10 c-md-6 c-lg-4 c-center">
            <div class="card p-25-all">
                <h1 class="fs-12 fw-700 m-0-b">Painel Admin</h1>
                <p class="fs-7 m-10-t m-20-b">Entre com as credenciais definidas no arquivo .env.</p>

                <?php foreach (($flashMessages ?? []) as $message): ?>
                    <?php if (($message['type'] ?? '') === 'error'): ?>
                        <div class="alert alert-danger p-15-all m-15-b" role="alert">
                            <?= htmlspecialchars((string) ($message['message'] ?? 'Erro ao autenticar.'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <form method="post" action="/login" class="f-col f-gap-15">
                    <?= \App\Support\Csrf::hiddenInput() ?>
                    <label class="f-col f-gap-5" for="username">
                        <span class="fw-600">Usuario</span>
                        <input id="username" class="form-control" type="text" name="username" autocomplete="username" required>
                    </label>

                    <label class="f-col f-gap-5" for="password">
                        <span class="fw-600">Senha</span>
                        <input id="password" class="form-control" type="password" name="password" autocomplete="current-password" required>
                    </label>

                    <button class="btn alert-info w-100" type="submit">Entrar</button>
                </form>
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
            duration: 3000,
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
