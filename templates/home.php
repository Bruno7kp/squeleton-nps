<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NPS Showcase | Squeleton.dev</title>
    <link rel="stylesheet" href="https://cdn.squeleton.dev/squeleton.v4.min.css">
    <script src="https://cdn.squeleton.dev/squeleton-main.v4.min.js"></script>
    <style>
        :root {
            --showcase-ink: #132127;
            --showcase-teal: #0f7e6c;
            --showcase-cyan: #39b6c5;
            --showcase-bg-soft: #f6fbff;
        }

        body {
            background: radial-gradient(circle at 80% 10%, #d9fbff 0%, rgba(217, 251, 255, 0) 38%),
                        radial-gradient(circle at 10% 30%, #e8f9ef 0%, rgba(232, 249, 239, 0) 40%),
                        linear-gradient(180deg, #ffffff 0%, var(--showcase-bg-soft) 100%);
            color: var(--showcase-ink);
        }

        .hero-shell {
            border: 1px solid #e2eff6;
            border-radius: 18px;
            background: linear-gradient(145deg, #ffffff 0%, #f0fcff 50%, #ecfff7 100%);
            box-shadow: 0 22px 40px rgba(12, 41, 56, 0.12);
        }

        .pattern-grid {
            background-image: linear-gradient(rgba(17, 80, 97, 0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(17, 80, 97, 0.08) 1px, transparent 1px);
            background-size: 22px 22px;
            border-radius: 14px;
        }

        .metric-box {
            border: 1px solid #dce9f3;
            border-radius: 12px;
            background: #ffffff;
        }

        .showcase-card {
            border: 1px solid #dce9f3;
            border-radius: 14px;
            background: #ffffff;
        }

        .trigger-btn {
            border-radius: 999px;
        }

        .embla {
            overflow: hidden;
        }

        .embla__container {
            display: flex;
            gap: 14px;
        }

        .embla__slide {
            flex: 0 0 82%;
            min-width: 0;
        }

        @media (min-width: 992px) {
            .embla__slide {
                flex: 0 0 42%;
            }
        }
    </style>
</head>
<body>
    <main class="container p-30-t p-40-b">
        <section class="hero-shell p-25-all wow fadeInUp" data-wow-duration="0.8s">
            <div class="row gap-20 f-items-center">
                <div class="c-xs-12 c-md-7">
                    <p class="fw-700 ls-2 text-uppercase fs-4 m-0-b" style="color:var(--showcase-teal);">NPS Platform Showcase</p>
                    <h1 class="fs-14 fw-800 lh-1-1 m-10-t m-0-b">Experiencia NPS em tempo real com Squeleton.dev</h1>
                    <p class="fs-8 lh-1-6 m-15-t m-0-b" style="max-width: 58ch;">
                        Demonstre gatilhos de pesquisa, renderizacao dinamica de widget e telemetria de respostas em uma unica vitrine.
                    </p>
                    <div class="f-row f-gap-10 m-20-t xs-f-col xs-f-items-start">
                        <button class="btn alert-info trigger-btn" data-trigger="on_load">Simular on_load</button>
                        <button class="btn alert-info trigger-btn" data-trigger="before_cancel">Simular before_cancel</button>
                        <button class="btn alert-info trigger-btn" data-trigger="after_completed_video">Simular after_completed_video</button>
                    </div>
                </div>
                <div class="c-xs-12 c-md-5">
                    <div class="pattern-grid p-20-all">
                        <h2 class="fs-9 fw-700 m-0-b">Acesso rapido</h2>
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
                    <p class="fs-12 fw-800 m-10-t m-0-b"><span data-counter="projects">0</span></p>
                </div>
            </div>
            <div class="c-xs-12 c-sm-6 c-lg-3">
                <div class="metric-box p-15-all">
                    <p class="text-uppercase fw-700 fs-4 m-0-b">Pesquisas</p>
                    <p class="fs-12 fw-800 m-10-t m-0-b"><span data-counter="surveys">0</span></p>
                </div>
            </div>
            <div class="c-xs-12 c-sm-6 c-lg-3">
                <div class="metric-box p-15-all">
                    <p class="text-uppercase fw-700 fs-4 m-0-b">Respostas</p>
                    <p class="fs-12 fw-800 m-10-t m-0-b"><span data-counter="submissions">0</span></p>
                </div>
            </div>
            <div class="c-xs-12 c-sm-6 c-lg-3">
                <div class="metric-box p-15-all">
                    <p class="text-uppercase fw-700 fs-4 m-0-b">Media NPS</p>
                    <p class="fs-12 fw-800 m-10-t m-0-b"><span data-counter="avg_nps">0</span></p>
                </div>
            </div>
        </section>

        <section class="m-30-t wow fadeInUp" data-wow-delay="0.2s">
            <h2 class="fs-11 fw-800 m-0-b">Demonstracao de pesquisas</h2>
            <p class="m-8-t m-15-b">Slides de modelos de abordagem para diferentes gatilhos.</p>
            <div class="embla" id="survey-carousel">
                <div class="embla__container">
                    <article class="embla__slide showcase-card p-20-all">
                        <h3 class="fs-8 fw-700 m-0-b">On Load - Primeira impressao</h3>
                        <p class="m-10-t m-0-b">Capta o sentimento inicial nos primeiros segundos da sessao.</p>
                    </article>
                    <article class="embla__slide showcase-card p-20-all">
                        <h3 class="fs-8 fw-700 m-0-b">After Completed Video</h3>
                        <p class="m-10-t m-0-b">Dispara avaliacao de valor percebido apos consumir conteudo.</p>
                    </article>
                    <article class="embla__slide showcase-card p-20-all">
                        <h3 class="fs-8 fw-700 m-0-b">Before Cancel</h3>
                        <p class="m-10-t m-0-b">Detecta risco de churn antes de encerrar assinatura.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="m-30-t wow fadeInUp" data-wow-delay="0.25s">
            <div class="showcase-card p-20-all">
                <h2 class="fs-10 fw-800 m-0-b">Video trigger ao finalizar YouTube</h2>
                <p class="m-10-t m-15-b">Ao terminar o video, o gatilho <strong>after_completed_video</strong> abre o widget automaticamente.</p>
                <div id="yt-player" style="width:100%;max-width:960px;aspect-ratio:16/9;border-radius:10px;overflow:hidden;background:#111;"></div>
            </div>
        </section>
    </main>

    <script src="https://cdn.squeleton.dev/squeleton-scripts.v4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/embla-carousel/embla-carousel.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/counterup2@2.0.2/dist/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/wowjs@1.1.3/dist/wow.min.js"></script>
    <script src="/widget-loader.js" data-nps-key="nps_pk_acme_demo_001" data-nps-trigger="on_load" data-nps-auto-open="false"></script>
    <script>
        (function () {
            var wowReady = typeof WOW !== 'undefined';
            if (wowReady) {
                new WOW().init();
            }

            if (typeof EmblaCarousel === 'function') {
                EmblaCarousel(document.getElementById('survey-carousel'), { loop: true, align: 'start' });
            }

            fetch('/api/showcase/stats')
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
