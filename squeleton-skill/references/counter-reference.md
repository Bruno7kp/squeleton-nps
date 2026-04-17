# Counter-Up2 Reference

Efeito de contagem animada que exibe números aumentando gradualmente até um valor final.

## Formatos Suportados

- **Inteiros**: 1234
- **Flutuadores**: 1.234 ou 99.9
- **Com vírgulas**: 1,234.56
- **Vírgulas e pontos**: 12.345,67
- **Com símbolos**: $1,234.56 ou 1500+

## Opções

| Opção | Tipo | Padrão | Descrição |
|-------|------|--------|-----------|
| `duration` | number | 1000 | Duração da animação em ms |
| `delay` | number | 16 | Delay entre cada frame |

## Exemplo

```html
<div id="stats-section" class="container">
    <div class="row text-center">
        <div class="c-xs-12 c-sm-4">
            <div class="stat-card p-25-all">
                <div class="fs-12 fw-700">
                    <span class="animated-number">1500</span>+
                </div>
                <div>Clientes Satisfeitos</div>
            </div>
        </div>
        <div class="c-xs-12 c-sm-4">
            <div class="stat-card p-25-all">
                <div class="fs-12 fw-700">
                    <span class="animated-number">99.9</span>%
                </div>
                <div>Uptime</div>
            </div>
        </div>
        <div class="c-xs-12 c-sm-4">
            <div class="stat-card p-25-all">
                <div class="fs-12 fw-700">
                    <span class="animated-number">500</span>+
                </div>
                <div>Projetos Entregues</div>
            </div>
        </div>
    </div>
</div>

<script>
    var statsSection = document.getElementById('stats-section');
    if (statsSection) {
        const counterUp = window.counterUp.default;
        const counters = document.querySelectorAll('.animated-number');

        const counterCall = entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('is-visible')) {
                    for (const counter of counters) {
                        counterUp(counter, { duration: 1000, delay: 16 })
                    }
                    entry.target.classList.add('is-visible')
                }
            })
        }

        const IO = new IntersectionObserver(counterCall, { threshold: 1 });
        IO.observe(statsSection);
    }
</script>
```
