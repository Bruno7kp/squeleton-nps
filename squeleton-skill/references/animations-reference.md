# Animations Reference

**WOW já vem inicializado automaticamente** com `offset: 0`, `mobile: false` e `live: true`.

## Uso com WOW (ativa ao entrar no viewport)

```html
<div class="wow fadeInUp">Conteúdo</div>
<div class="wow bounceIn" data-wow-delay="0.2s">Com delay</div>
<div class="wow pulse" data-wow-duration="2s">Duração customizada</div>
<div class="wow fadeIn" data-wow-iteration="3">3 vezes</div>
<div class="wow zoomIn" data-wow-offset="100">Offset de 100px</div>
```

## Uso com Animated (ativa imediatamente)

```html
<div class="animated fadeIn">Imediato</div>
<div class="animated pulse infinite">Loop infinito</div>
<div class="animated zoomIn delay-500 duration-2000">Com classes auxiliares</div>
```

## Animações de Entrada - Fade

```
fadeIn
fadeInUp
fadeInDown
fadeInLeft
fadeInRight
fadeOut
```

## Animações de Entrada - Zoom

```
zoomIn
zoomOut
```

## Animações de Entrada - Bounce

```
bounceIn
```

## Animações de Entrada - Back

```
backInDown
backInUp
backInLeft
backInRight
```

## Animações de Entrada - Especiais

```
rotateIn
flipIn
glowIn
slideIn
popIn
liquidIn
magnetIn
floatIn
waveIn
```

## Animações de Atenção

```
flash
pulse
shakeX
swing
tada
```

## Animações Contínuas (já configuradas)

```
anima-pulse (box-shadow pulsante)
anima-shake (tremor sutil)
anima-heart (batimento cardíaco)
anima-skeleton (loading skeleton)
```

## Classes de Delay

```
delay-100 (100ms)
delay-200 (200ms)
delay-250 (250ms)
delay-300 (300ms)
delay-400 (400ms)
delay-500 (500ms)
delay-600 (600ms)
delay-700 (700ms)
delay-800 (800ms)
delay-900 (900ms)
delay-1000 (1s)
delay-1250 (1.25s)
delay-1500 (1.5s)
delay-1750 (1.75s)
delay-2000 (2s)
delay-2500 (2.5s)
delay-3000 (3s)
delay-4000 (4s)
delay-5000 (5s)
```

## Classes de Duração

```
duration-500 (0.5s)
duration-1000 (1s)
duration-1250 (1.25s)
duration-1500 (1.5s)
duration-2000 (2s)
duration-2500 (2.5s)
duration-3000 (3s)
```

## Classes de Repetição

```
repeat-2 (2x)
repeat-3 (3x)
repeat-4 (4x)
repeat-5 (5x)
infinite (infinito)
```

## Classes de Direção

```
reverse
alternate
alternate-reverse
```

## Classes de Estado

```
paused
running
```

## Atributos data-wow-*

| Atributo | Descrição | Exemplo |
|----------|-----------|---------|
| `data-wow-delay` | Atraso antes da animação | `"0.3s"` |
| `data-wow-duration` | Duração da animação | `"1.5s"` |
| `data-wow-iteration` | Número de repetições | `"2"` |
| `data-wow-offset` | Distância do viewport para ativar | `"100"` |

## Skeleton Loading

```html
<!-- Placeholder de carregamento -->
<div class="anima-skeleton bg-light-gray border-rd-4 h-20px w-200px"></div>
```

## Animações de Modal

```
fadeModal
scaleModal
slideModal
```

## Outras Animações Utilitárias

```
sk-pulse (spinner circular)
preloader-spin (rotação contínua)
marquee (rolagem horizontal)
anima-width (expansão de largura 0 a 100%)
```

## Exemplos de Uso

```html
<!-- Cards com entrada escalonada -->
<div class="row">
    <div class="c-xs-12 c-md-4 wow fadeInUp">Card 1</div>
    <div class="c-xs-12 c-md-4 wow fadeInUp" data-wow-delay="0.1s">Card 2</div>
    <div class="c-xs-12 c-md-4 wow fadeInUp" data-wow-delay="0.2s">Card 3</div>
</div>

<!-- Botão com pulso de atenção -->
<button class="anima-pulse">Clique aqui</button>

<!-- Ícone com shake periódico -->
<span class="iccon-bell-2 anima-shake"></span>

<!-- Loading skeleton -->
<div class="anima-skeleton bg-light-gray border-rd-4 h-150px"></div>

<!-- Hero com animação de entrada -->
<section class="h-100vh">
    <h1 class="wow fadeInDown">Título</h1>
    <p class="wow fadeInUp" data-wow-delay="0.3s">Subtítulo</p>
</section>

<!-- Animação infinita com pausa -->
<div class="animated pulse infinite paused" id="elemento">
    Controlado via JS
</div>
<script>
    // Para iniciar: element.classList.remove('paused')
</script>
```
