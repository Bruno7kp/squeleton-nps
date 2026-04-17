# Flexbox Reference

## Breakpoints Responsivos

Todas as classes flex possuem variantes responsivas com os prefixos:
- `xs-` (mobile: < 576px)
- `sm-` (tablet: < 768px)
- `md-` (desktop: < 992px)

Exemplo: `f-col` → `xs-f-col`, `sm-f-col`, `md-f-col`

---

## Container Flex

```
d-flex
f-grid (display: flex + flex-wrap: wrap + flex-direction: row)
```

## Direção

```
f-row
f-row-reverse
f-col
f-col-reverse
```

## Wrap

```
f-wrap
f-nowrap
f-wrap-reverse
```

## Align Items (eixo cruzado)

```
f-items-start
f-items-end
f-items-end-safe
f-items-center
f-items-center-safe
f-items-baseline
f-items-baseline-last
f-items-stretch
```

## Justify Content (eixo principal)

```
f-justify-start
f-justify-end
f-justify-end-safe
f-justify-center
f-justify-center-safe
f-justify-between
f-justify-around
f-justify-evenly
f-justify-stretch
f-justify-baseline
f-justify-normal
```

## Align Self (item individual)

```
f-self-auto
f-self-start
f-self-end
f-self-center
f-self-center-safe
f-self-stretch
f-self-baseline
```

## Flex Grow

```
f-grow-1
f-grow-2
f-grow-3
```

## Flex Shrink

```
f-shrink-0
f-shrink-1
f-shrink-2
```

## Flex Basis

```
f-basis-0
```

## Flex Shorthand

```
f-auto-max (flex: 1 1 auto)
f-auto-min (flex: 0 1 auto)
```

## Align Content (múltiplas linhas com wrap)

Controla espaçamento entre **linhas** quando `flex-wrap: wrap` cria múltiplas linhas. Diferente de `align-items` que alinha itens **dentro** de cada linha.

```
f-content-start (flex-start - linhas no início)
f-content-end (flex-end - linhas no final)
f-content-center (center - linhas centralizadas)
f-content-between (space-between - espaço entre linhas)
f-content-around (space-around - espaço ao redor das linhas)
f-content-stretch (stretch - linhas esticam para preencher - padrão)
```

**Quando usar:**
- Container com `f-wrap` ou `f-grid` (que já tem wrap)
- Container tem altura definida (`h-400px`, `h-100vh`, etc.)
- Itens quebram em múltiplas linhas

## Gap (espaçamento entre itens)

```
f-gap-0 (sem gap)
f-gap-5 (5px)
f-gap-10 (10px)
f-gap-15 (15px)
f-gap-20 (20px)
f-gap-25 (25px)
f-gap-30 (30px)
f-gap-35 (35px)
f-gap-40 (40px)
f-gap-45 (45px)
f-gap-50 (50px)
```

## Exemplos de Uso

```html
<!-- Centralizar conteúdo vertical e horizontal -->
<div class="d-flex f-items-center f-justify-center h-100vh">
    Centralizado
</div>

<!-- Header com logo e menu -->
<header class="d-flex f-items-center f-justify-between">
    <div class="logo">Logo</div>
    <nav class="d-flex f-gap-20">Links</nav>
</header>

<!-- Grid flexível com gap -->
<div class="f-grid f-gap-15">
    <div class="f-grow-1">Item 1</div>
    <div class="f-grow-1">Item 2</div>
    <div class="f-grow-1">Item 3</div>
</div>

<!-- Coluna no mobile, linha no desktop -->
<div class="d-flex xs-f-col md-f-row f-gap-20">
    <div>Sidebar</div>
    <div class="f-grow-1">Conteúdo</div>
</div>

<!-- Item que não encolhe -->
<div class="d-flex">
    <div class="f-shrink-0 w-200px">Fixo</div>
    <div class="f-grow-1">Flexível</div>
</div>

<!-- Grid com múltiplas linhas - espaçar verticalmente -->
<div class="f-grid f-content-between h-400px">
    <div class="w-50">Item 1</div>
    <div class="w-50">Item 2</div>
    <div class="w-50">Item 3</div>
    <div class="w-50">Item 4</div>
</div>
```
