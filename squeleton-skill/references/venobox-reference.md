# VenoBox Reference

Lightbox para imagens, vídeos, galerias e iframes. **Já vem inicializado automaticamente.**

Documentação oficial: https://veno.es/venobox

## Seletores (já inicializados)

```
open-gallery (imagens e galerias)
open-video (vídeos YouTube, Vimeo, Bunny Stream)
open-iframe (iframes)
```

## Imagem Simples

```html
<a class="open-gallery" href="imagem-grande.jpg">
    <img src="thumb.jpg" alt="Descrição">
</a>
```

## Galeria de Imagens

Use `data-gall` com mesmo valor para agrupar:

```html
<a class="open-gallery" href="foto1.jpg" data-gall="galeria1">
    <img src="thumb1.jpg">
</a>
<a class="open-gallery" href="foto2.jpg" data-gall="galeria1">
    <img src="thumb2.jpg">
</a>
<a class="open-gallery" href="foto3.jpg" data-gall="galeria1">
    <img src="thumb3.jpg">
</a>
```

## Vídeo YouTube

```html
<a class="open-video" data-vbtype="video" href="https://www.youtube.com/watch?v=VIDEO_ID">
    Assistir vídeo
</a>

<!-- Com autoplay -->
<a class="open-video" data-vbtype="video" data-autoplay="true" href="https://www.youtube.com/watch?v=VIDEO_ID">
    Assistir vídeo
</a>
```

## Vídeo Vimeo

```html
<a class="open-video" data-vbtype="video" href="https://vimeo.com/VIDEO_ID">
    Assistir vídeo
</a>
```

## Vídeo MP4 (self-hosted)

```html
<a class="open-video" data-vbtype="video" href="/videos/meu-video.mp4">
    Assistir vídeo
</a>
```

## iFrame

```html
<a class="open-iframe" data-vbtype="iframe" href="https://exemplo.com/pagina">
    Abrir página
</a>

<!-- Com largura máxima -->
<a class="open-iframe" data-vbtype="iframe" data-maxwidth="800px" href="pagina.html">
    Abrir
</a>
```

## Atributos data-*

| Atributo | Descrição | Valores |
|----------|-----------|---------|
| `data-vbtype` | Tipo de conteúdo | `video`, `iframe` |
| `data-gall` | Agrupar em galeria | qualquer string |
| `data-autoplay` | Autoplay para vídeos | `true`, `false` |
| `data-maxwidth` | Largura máxima | `800px`, `90%`, etc |
| `data-title` | Título do lightbox | texto |

## Exemplos Práticos

```html
<!-- Grid de galeria -->
<div class="row gap-10">
    <div class="c-xs-6 c-sm-4 c-md-3">
        <a class="open-gallery" href="foto1-full.jpg" data-gall="portfolio">
            <img src="foto1-thumb.jpg" class="w-100 border-rd-8">
        </a>
    </div>
    <div class="c-xs-6 c-sm-4 c-md-3">
        <a class="open-gallery" href="foto2-full.jpg" data-gall="portfolio">
            <img src="foto2-thumb.jpg" class="w-100 border-rd-8">
        </a>
    </div>
</div>

<!-- Botão de vídeo sobre imagem -->
<div class="ps-relative">
    <img src="video-thumb.jpg" class="w-100">
    <a class="open-video absolute-xy" data-vbtype="video" data-autoplay="true" href="https://youtube.com/watch?v=xxx">
        <span class="iccon-play-1 more-50"></span>
    </a>
</div>
```
