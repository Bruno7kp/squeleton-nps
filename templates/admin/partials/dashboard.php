<section>
    <div class="f-col f-gap-15">
        <div>
            <h2 class="fs-10 fw-700 m-0-b">Dashboard (Base)</h2>
            <p class="m-10-t m-0-b">Fase 2 concluida: autenticacao, middleware e estrutura HTMX do admin.</p>
        </div>

        <div class="row gap-15">
            <article class="c-xs-12 c-sm-6 c-lg-4">
                <div class="card p-15-all">
                    <p class="fs-4 fw-700 text-uppercase ls-1 m-0-b">Sessao</p>
                    <p class="fs-8 m-10-t m-0-b">Ativa e protegida por middleware</p>
                </div>
            </article>

            <article class="c-xs-12 c-sm-6 c-lg-4">
                <div class="card p-15-all">
                    <p class="fs-4 fw-700 text-uppercase ls-1 m-0-b">HTMX</p>
                    <p class="fs-8 m-10-t m-0-b">Painel carregado por partial sem refresh</p>
                </div>
            </article>

            <article class="c-xs-12 c-sm-6 c-lg-4">
                <div class="card p-15-all">
                    <p class="fs-4 fw-700 text-uppercase ls-1 m-0-b">Usuario atual</p>
                    <p class="fs-8 m-10-t m-0-b"><?= htmlspecialchars((string) (($user['username'] ?? 'admin')), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </article>
        </div>
    </div>
</section>
