<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vitrine NPS | Squeleton.dev</title>
    <link rel="stylesheet" href="https://cdn.squeleton.dev/squeleton.v4.min.css">
    <link rel="stylesheet" href="/home.css">
    <script src="https://cdn.squeleton.dev/squeleton-main.v4.min.js"></script>
</head>
<body>
    <main class="container p-30-t p-40-b">
        <section class="hero-shell p-25-all wow fadeInUp" data-wow-duration="0.8s">
            <div class="row gap-20 f-items-center">
                <div class="c-xs-12 c-md-7">
                    <p class="fw-700 ls-2 text-uppercase fs-4 m-0-b" style="color:var(--showcase-teal);">Vitrine da Plataforma NPS</p>
                    <h1 class="fs-14 fw-800 lh-1-1 m-10-t m-0-b">Experiência NPS em tempo real com Squeleton.dev</h1>
                    <p class="fs-8 lh-1-6 m-15-t m-0-b" style="max-width: 58ch;">
                        Demonstre gatilhos de pesquisa, renderização dinâmica de widget e telemetria de respostas em uma única vitrine.
                    </p>
                    <div class="f-row f-gap-10 m-20-t xs-f-col xs-f-items-start">
                        <button class="btn alert-info trigger-btn" data-trigger="on_load">Simular gatilho de carregamento</button>
                        <button class="btn alert-info trigger-btn" data-trigger="before_cancel">Simular antes do cancelamento</button>
                        <button class="btn alert-info trigger-btn" data-trigger="after_completed_video">Simular após concluir vídeo</button>
                    </div>
                </div>
                <div class="c-xs-12 c-md-5">
                    <div class="pattern-grid p-20-all">
                        <h2 class="fs-9 fw-700 m-0-b">Acesso rápido</h2>
                        <ul class="m-10-t m-0-b p-20-l">
                            <li>Health web: /health</li>
                            <li>Health API: /api/health</li>
                            <li>Admin: /admin</li>
                            <li>Login: /login</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section class="row gap-15 m-25-t wow fadeInUp" data-wow-delay="0.15s">
            <div class="c-xs-12 c-sm-6 c-lg-3">
                <div class="metric-box p-15-all">
                    <p class="text-uppercase fw-700 fs-4 m-0-b">Projetos</p>
                    <p class="fs-12 fw-800 m-10-t m-0-b"><span data-counter="projects"><?= (int) (($homeStats['projects'] ?? 0)) ?></span></p>
                </div>
            </div>
            <div class="c-xs-12 c-sm-6 c-lg-3">
                <div class="metric-box p-15-all">
                    <p class="text-uppercase fw-700 fs-4 m-0-b">Pesquisas</p>
                    <p class="fs-12 fw-800 m-10-t m-0-b"><span data-counter="surveys"><?= (int) (($homeStats['surveys'] ?? 0)) ?></span></p>
                </div>
            </div>
            <div class="c-xs-12 c-sm-6 c-lg-3">
                <div class="metric-box p-15-all">
                    <p class="text-uppercase fw-700 fs-4 m-0-b">Respostas</p>
                    <p class="fs-12 fw-800 m-10-t m-0-b"><span data-counter="submissions"><?= (int) (($homeStats['submissions'] ?? 0)) ?></span></p>
                </div>
            </div>
            <div class="c-xs-12 c-sm-6 c-lg-3">
                <div class="metric-box p-15-all">
                    <p class="text-uppercase fw-700 fs-4 m-0-b">Média NPS</p>
                    <p class="fs-12 fw-800 m-10-t m-0-b"><span data-counter="avg_nps"><?= (float) (($homeStats['avg_nps'] ?? 0)) ?></span></p>
                </div>
            </div>
        </section>

        <section class="m-30-t wow fadeInUp" data-wow-delay="0.2s">
            <h2 class="fs-11 fw-800 m-0-b">Demonstração de pesquisas</h2>
            <p class="m-8-t m-15-b">Slides de modelos de abordagem para diferentes gatilhos.</p>
            <div class="embla" id="survey-carousel">
                <div class="embla__container">
                    <article class="embla__slide showcase-card p-20-all">
                        <h3 class="fs-8 fw-700 m-0-b">No carregamento - Primeira impressão</h3>
                        <p class="m-10-t m-0-b">Capta o sentimento inicial nos primeiros segundos da sessão.</p>
                    </article>
                    <article class="embla__slide showcase-card p-20-all">
                        <h3 class="fs-8 fw-700 m-0-b">Após concluir vídeo</h3>
                        <p class="m-10-t m-0-b">Dispara avaliação de valor percebido após consumir conteúdo.</p>
                    </article>
                    <article class="embla__slide showcase-card p-20-all">
                        <h3 class="fs-8 fw-700 m-0-b">Antes do cancelamento</h3>
                        <p class="m-10-t m-0-b">Ajuda a reduzir a taxa de cancelamento antes de encerrar a assinatura.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="m-30-t wow fadeInUp" data-wow-delay="0.25s">
            <div class="showcase-card p-20-all">
                <h2 class="fs-10 fw-800 m-0-b">Disparo ao finalizar vídeo no YouTube</h2>
                <p class="m-10-t m-15-b">Ao terminar o video, o gatilho <strong>after_completed_video</strong> abre o widget automaticamente.</p>
                <div id="yt-player" style="width:100%;max-width:960px;aspect-ratio:16/9;border-radius:10px;overflow:hidden;background:#111;"></div>
            </div>
        </section>
    </main>

    <script src="https://cdn.squeleton.dev/squeleton-scripts.v4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/embla-carousel/embla-carousel.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/counterup2@2.0.2/dist/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/wowjs@1.1.3/dist/wow.min.js"></script>
    <script src="/widget-loader.js" data-nps-key="nps_pk_acme_demo_001" data-nps-trigger="on_load" data-nps-auto-open="false" ></script>
    <script>
        (function () {
            var wowReady = typeof WOW !== 'undefined';
            if (wowReady) {
                new WOW().init();
            }

            if (typeof EmblaCarousel === 'function') {
                EmblaCarousel(document.getElementById('survey-carousel'), { loop: true, align: 'start' });
            }

            fetch('/api/showcase/stats?t=' + Date.now(), { cache: 'no-store' })
                .then(function (response) { return response.json(); })
                .then(function (result) {
                    if (!result.success || !result.data) {
                        return;
                    }

                    Object.keys(result.data).forEach(function (key) {
                        var target = document.querySelector('[data-counter="' + key + '"]');
                        if (!target) {
                            return;
                        }

                        var value = result.data[key] == null ? 0 : result.data[key];
                        target.textContent = String(value);

                        if (window.counterUp) {
                            counterUp(target, {
                                duration: 900,
                                delay: 12,
                            });
                        }
                    });
                })
                .catch(function () {});

            document.querySelectorAll('[data-trigger]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var trigger = button.getAttribute('data-trigger') || 'on_load';
                    if (window.NPSWidget && typeof window.NPSWidget.openWithTrigger === 'function') {
                        window.NPSWidget.openWithTrigger(trigger);
                    }
                });
            });

            var ytScript = document.createElement('script');
            ytScript.src = 'https://www.youtube.com/iframe_api';
            document.head.appendChild(ytScript);

            window.onYouTubeIframeAPIReady = function () {
                var player = new YT.Player('yt-player', {
                    host: 'https://www.youtube.com',
                    videoId: 'DMrJUyg7TYM',
                    playerVars: {
                        origin: window.location.origin,
                        enablejsapi: 1,
                        rel: 0,
                        modestbranding: 1,
                    },
                    events: {
                        onStateChange: function (event) {
                            if (event.data === YT.PlayerState.ENDED && window.NPSWidget && typeof window.NPSWidget.openWithTrigger === 'function') {
                                window.NPSWidget.openWithTrigger('after_completed_video');
                            }
                        }
                    }
                });

                window.__npsVideoPlayer = player;
            };
        })();
    </script>
</body>
</html>
