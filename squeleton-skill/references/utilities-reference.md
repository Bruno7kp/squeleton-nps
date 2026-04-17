# Utilities Reference

Classes utilitárias diversas para posicionamento, visibilidade, cursor, background, float e outras propriedades.

## Position

```
ps-relative
ps-absolute
ps-fixed
ps-sticky
ps-static
ps-inherit
```

Responsivos:
```
md-ps-relative, md-ps-absolute, md-ps-fixed, md-ps-sticky, md-ps-static
sm-ps-relative, sm-ps-absolute, sm-ps-fixed, sm-ps-sticky, sm-ps-static
xs-ps-relative, xs-ps-absolute, xs-ps-fixed, xs-ps-sticky, xs-ps-static
```

## Position Values

```
top-0
top-auto
bottom-0
bottom-auto
left-0
left-auto
right-0
right-auto
```

## Centralização Absoluta

```
absolute-x (left: 50%, transform: translateX(-50%))
absolute-y (top: 50%, transform: translateY(-50%))
absolute-xy (left: 50%, top: 50%, transform: translate(-50%, -50%))
```

## Visibility

```
vs-visible (visibility: visible)
vs-hidden (visibility: hidden)
hidden (display: none)
```

## Visibilidade por Breakpoint

```
visible-xs, visible-sm, visible-md, visible-lg (display: none por padrão, visível no breakpoint)
```

Nota: Essas classes ficam ocultas por padrão e são exibidas apenas no breakpoint específico via media query.

## Cursor

Classes utilitárias para modificar o cursor em elementos interativos.

```
cursor-pointer       (hover em botões/cards clicáveis)
cursor-not-allowed   (estados disabled)
cursor-wait          (loading states)
cursor-text          (campos de texto customizados)
cursor-move          (elementos movíveis/drag & drop)
cursor-grab          (carrosséis, sliders - antes de arrastar)
cursor-grabbing      (durante o arrasto)
cursor-help          (tooltips, ícones de ajuda)
cursor-zoom-in       (lightbox, zoom de imagens)
cursor-zoom-out      (lightbox em zoom)
```

**Exemplos**:
```html
<!-- Botão disabled -->
<button class="cursor-not-allowed opacity-5" disabled>Indisponível</button>

<!-- Card clicável -->
<div class="cursor-pointer" onclick="openDetail()">Card interativo</div>

<!-- Carrossel com drag -->
<div class="slide__row cursor-grab">
  <div class="slide__item">Slide 1</div>
</div>

<!-- Durante arrasto (via JavaScript) -->
<div class="slide__row cursor-grabbing">Arrastando...</div>

<!-- Loading -->
<div class="cursor-wait">Processando...</div>

<!-- Zoom de imagem -->
<img class="cursor-zoom-in" onclick="openLightbox()">

<!-- Help icon -->
<i class="icon-help cursor-help" title="Ajuda"></i>
```

## Overflow

```
ov-hidden (overflow: hidden)
ov-visible (overflow: visible)
ov-auto (overflow: auto)
```

Responsivos:
```
md-ov-hidden, md-ov-visible, md-ov-auto
sm-ov-hidden, sm-ov-visible, sm-ov-auto
xs-ov-hidden, xs-ov-visible, xs-ov-auto
```

## Float e Clear

```
float-left
float-right
float-none
clear-both
```

Responsivos:
```
md-float-left, md-float-right, md-float-none, md-clear-both
sm-float-left, sm-float-right, sm-float-none, sm-clear-both
xs-float-left, xs-float-right, xs-float-none, xs-clear-both
```

## Vertical Align

```
v-align-top
v-align-middle
v-align-bottom
```

Responsivos:
```
md-v-align-top, md-v-align-middle, md-v-align-bottom
sm-v-align-top, sm-v-align-middle, sm-v-align-bottom
xs-v-align-top, xs-v-align-middle, xs-v-align-bottom
```

## Background Position

```
bg-cover (imagem de fundo com cover e centralizada)
bg-parallax (imagem de fundo com efeito parallax, inicia do topo)
bg-ps-left (background-position: left center)
bg-ps-left-bottom (background-position: left bottom)
bg-ps-right (background-position: right center)
bg-ps-top (background-position: center top)
bg-ps-bottom (background-position: center bottom)
bg-ps-x-50 (background-position-x: 50%)
```

## Background Repeat

```
bg-repeat (background-repeat: repeat)
bg-repeat-x (background-repeat: repeat-x)
bg-repeat-y (background-repeat: repeat-y)
bg-repeat-space (background-repeat: space)
bg-repeat-round (background-repeat: round)
bg-no-repeat (background-repeat: no-repeat)
```

## Embed Responsivo

```html
<div class="embed-responsive embed-responsive-16by9">
    <iframe src="..."></iframe>
</div>

<div class="embed-responsive embed-responsive-4by3">
    <iframe src="..."></iframe>
</div>
```

## Video Responsivo

```html
<div class="video-responsive">
    <iframe src="..."></iframe>
</div>
```

## Object Fit

```
obj-cover (object-fit: cover)
obj-contain (object-fit: contain)
obj-fill (object-fit: fill)
```

## Aspect Ratio

```
aspect-1-1 (1:1 - quadrado)
aspect-16-9 (16:9 - vídeo widescreen)
aspect-4-3 (4:3 - vídeo clássico)
aspect-3-2 (3:2 - fotografia)
```

## Text Overflow

```
truncate (overflow: hidden + text-overflow: ellipsis + white-space: nowrap)
line-clamp-2 (limita a 2 linhas com ellipsis)
line-clamp-3 (limita a 3 linhas com ellipsis)
```

## Pointer Events

```
pe-none (pointer-events: none - ignora cliques)
pe-auto (pointer-events: auto - comportamento padrão)
```

## Exemplos de Uso

```html
<!-- Elemento fixo no canto -->
<div class="ps-fixed top-0 right-0 p-20-all">
    Fixo no canto
</div>

<!-- Centralizar absolutamente -->
<div class="ps-relative h-300px">
    <div class="absolute-xy">Centralizado</div>
</div>

<!-- Background cover -->
<div class="bg-cover h-400px" style="background-image: url('imagem.jpg')">
    Conteúdo sobre imagem
</div>

<!-- Vídeo responsivo 16:9 -->
<div class="video-responsive">
    <iframe src="https://youtube.com/embed/xxx"></iframe>
</div>

<!-- Esconder overflow no mobile -->
<div class="xs-ov-hidden md-ov-auto">
    Conteúdo
</div>
```
