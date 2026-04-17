# Grid Reference

Sistema de grid de 12 colunas baseado em Flexbox.

## Containers

```
container (max-width: 1250px, centralizado)
container-fluid (width: 100%, max-width: 1920px em ultra-wide)
```

## Quando Usar Cada Container

### `.container` (max-width: 1250px)
- Conteúdo de texto (blogs, artigos)
- Grids de cards/produtos
- Formulários
- Seções com conteúdo que precisa de foco

### `.container-fluid` (100% width)
- Grids de imagens/galerias que precisam ocupar 100% da largura
- Tabelas ou dashboards que precisam de mais espaço horizontal
- Conteúdo que não deve ter margem lateral (mapas, vídeos full-width)
- **Nota:** Em telas ≥1920px, limita a max-width: 1920px

## Row e Gap

```
row (display: flex, flex-wrap: wrap, gap: 30px)
```

O `.row` é um container flex com gap padrão de 30px entre colunas.

- O gap aplica espaçamento **horizontal e vertical** automaticamente
- Quando colunas quebram de linha, o espaçamento vertical é aplicado sem necessidade de margin extra
- Use classes `gap-{valor}` para alterar o valor padrão.

### Classes de Gap

```
gap-0 (0px)
gap-5 (5px)
gap-10 (10px)
gap-15 (15px)
gap-20 (20px)
gap-25 (25px)
gap-30 (30px - padrão do row)
gap-35 (35px)
gap-40 (40px)
gap-45 (45px)
gap-50 (50px)
```

Responsivos:
```
xs-gap-{0-50} (intervalo de 5)
sm-gap-{0-50} (intervalo de 5)
md-gap-{0-50} (intervalo de 5)
```

### Exemplos de Row com Gap

```html
<!-- Row com gap padrão (30px) -->
<div class="row">
    <div class="c-xs-6">Coluna 1</div>
    <div class="c-xs-6">Coluna 2</div>
</div>

<!-- Row com gap menor -->
<div class="row gap-15">
    <div class="c-xs-4">Card 1</div>
    <div class="c-xs-4">Card 2</div>
    <div class="c-xs-4">Card 3</div>
</div>

<!-- Row sem gap -->
<div class="row gap-0">
    <div class="c-xs-6">Sem espaço</div>
    <div class="c-xs-6">Entre colunas</div>
</div>

<!-- Gap responsivo: 30px desktop, 15px mobile -->
<div class="row gap-30 xs-gap-15">
    <div class="c-xs-12 c-md-6">Card 1</div>
    <div class="c-xs-12 c-md-6">Card 2</div>
</div>
```

## Colunas

Padrão: `c-{breakpoint}-{1-12}`

**Nota**: Nas colunas, o breakpoint é **sufixo** (diferente das outras classes utilitárias).

### XS (≤639px) - Base mobile

```
c-xs-1 (8.33%)
c-xs-2 (16.67%)
c-xs-3 (25%)
c-xs-4 (33.33%)
c-xs-5 (41.67%)
c-xs-6 (50%)
c-xs-7 (58.33%)
c-xs-8 (66.67%)
c-xs-9 (75%)
c-xs-10 (83.33%)
c-xs-11 (91.67%)
c-xs-12 (100%)
```

### SM (≥640px)

```
c-sm-1 (8.33%)
c-sm-2 (16.67%)
c-sm-3 (25%)
c-sm-4 (33.33%)
c-sm-5 (41.67%)
c-sm-6 (50%)
c-sm-7 (58.33%)
c-sm-8 (66.67%)
c-sm-9 (75%)
c-sm-10 (83.33%)
c-sm-11 (91.67%)
c-sm-12 (100%)
```

### MD (≥992px)

```
c-md-1 (8.33%)
c-md-2 (16.67%)
c-md-3 (25%)
c-md-4 (33.33%)
c-md-5 (41.67%)
c-md-6 (50%)
c-md-7 (58.33%)
c-md-8 (66.67%)
c-md-9 (75%)
c-md-10 (83.33%)
c-md-11 (91.67%)
c-md-12 (100%)
```

### LG (≥1200px)

```
c-lg-1 (8.33%)
c-lg-2 (16.67%)
c-lg-3 (25%)
c-lg-4 (33.33%)
c-lg-5 (41.67%)
c-lg-6 (50%)
c-lg-7 (58.33%)
c-lg-8 (66.67%)
c-lg-9 (75%)
c-lg-10 (83.33%)
c-lg-11 (91.67%)
c-lg-12 (100%)
```

## Colunas Especiais

```
c-auto (flex: 1 1 0% - ocupa espaço disponível)
c-center (margin: 0 auto - centraliza a coluna)
md-c-center (responsivo ≤1199px)
sm-c-center (responsivo ≤991px)
xs-c-center (responsivo ≤639px)
```

## Exemplos de Uso

```html
<!-- Layout básico 2 colunas -->
<div class="container">
    <div class="row">
        <div class="c-xs-12 c-md-8">Conteúdo principal</div>
        <div class="c-xs-12 c-md-4">Sidebar</div>
    </div>
</div>

<!-- Grid de 3 colunas com gap -->
<div class="container">
    <div class="row gap-20">
        <div class="c-xs-12 c-sm-6 c-md-4">Card 1</div>
        <div class="c-xs-12 c-sm-6 c-md-4">Card 2</div>
        <div class="c-xs-12 c-sm-6 c-md-4">Card 3</div>
    </div>
</div>

<!-- Grid de 4 colunas -->
<div class="container">
    <div class="row">
        <div class="c-xs-6 c-sm-4 c-lg-3">Item 1</div>
        <div class="c-xs-6 c-sm-4 c-lg-3">Item 2</div>
        <div class="c-xs-6 c-sm-4 c-lg-3">Item 3</div>
        <div class="c-xs-6 c-sm-4 c-lg-3">Item 4</div>
    </div>
</div>

<!-- Coluna centralizada -->
<div class="container">
    <div class="row">
        <div class="c-xs-12 c-md-8 c-center">Conteúdo centralizado</div>
    </div>
</div>

<!-- Container fluido (100% largura) -->
<div class="container-fluid">
    <div class="row">
        <div class="c-xs-12">Full width</div>
    </div>
</div>

<!-- Sem gap entre colunas -->
<div class="row gap-0">
    <div class="c-xs-6">Sem espaço</div>
    <div class="c-xs-6">Entre colunas</div>
</div>
```

## Otimização de Performance - Content Visibility

Classes para lazy rendering de seções usando `content-visibility: auto`. Melhora performance de initial render em 5-7x.

### Classes Base (Variantes de Tamanho)

```
render-auto-small  (300px - seções compactas: CTAs, banners)
render-auto        (500px - seções padrão: features, grids)
render-auto-large  (800px - seções grandes: hero, galerias)
```

### Classes Responsivas (Breakpoints)

```
md-render-auto-small   (350px em ≤1199px)
md-render-auto         (550px em ≤1199px)
md-render-auto-large   (900px em ≤1199px)

sm-render-auto-small   (400px em ≤991px)
sm-render-auto         (650px em ≤991px)
sm-render-auto-large   (1000px em ≤991px)

xs-render-auto-small   (450px em ≤639px)
xs-render-auto         (750px em ≤639px)
xs-render-auto-large   (1200px em ≤639px)
```

### Quando Usar

Use em **`.container` ou `.container-fluid`**, NÃO em elementos individuais:
- Containers de conteúdo abaixo da dobra (below-the-fold)
- Containers com grids de cards
- Footer e containers finais da página
- Containers de FAQ, testimonials, features

**Não use** em:
- Elementos individuais (cards, items de lista)
- Conteúdo above-the-fold (primeira tela)
- Elementos com animações de hover (scale, translate, box-shadow)

### Por que não usar em elementos individuais?

O `content-visibility: auto` aplica implicitamente `contain: size layout paint`, que **corta overflow** de animações. Se um card tem `hover: scale(1.05)`, a parte que extrapola será cortada.

### Como Escolher a Classe

**Variantes de Tamanho** (`render-auto-small`, `render-auto`, `render-auto-large`):
- Use baseado na altura **estimada da seção**
- `.render-auto-small` → Seções compactas (300px)
- `.render-auto` → Seções médias (500px)
- `.render-auto-large` → Seções grandes/hero (800px)

**Variantes Responsivas** (`{md|sm|xs}-render-auto-{small|large}`):
- Use quando a seção muda de altura em breakpoints menores
- Padrão: `{breakpoint}-render-auto` para tamanho médio
- Adicione `-small` ou `-large` conforme a altura da seção no breakpoint

### Exemplos de Uso

```html
<!-- ✅ CORRETO - render-auto no container -->
<section>
    <div class="container render-auto">
        <div class="row">
            <div class="c-xs-12 c-sm-6 c-md-4">
                <div class="card">Produto 1</div>
            </div>
            <div class="c-xs-12 c-sm-6 c-md-4">
                <div class="card">Produto 2</div>
            </div>
        </div>
    </div>
</section>

<!-- ✅ Seção compacta (CTA) -->
<section>
    <div class="container render-auto-small text-center p-40-tb">
        <h2>Pronto para começar?</h2>
        <button>Cadastre-se</button>
    </div>
</section>

<!-- ✅ Seção grande (Hero/Galeria) -->
<section>
    <div class="container render-auto-large">
        <div class="row">
            <div class="c-xs-12 c-md-6">
                <img src="hero.jpg" class="w-100">
            </div>
            <div class="c-xs-12 c-md-6">
                <h1>Título Hero</h1>
            </div>
        </div>
    </div>
</section>

<!-- ❌ ERRADO - NÃO use no elemento individual -->
<div class="c-xs-12 c-md-4 render-auto">
    <div class="card hover-scale">...</div>
</div>
```

### Performance Esperada

Com `render-auto` aplicado a containers below-the-fold:
- **Initial render**: 5-7x mais rápido
- **Scroll performance**: Melhor FPS (seções renderizam sob demanda)
- **Memória**: Redução de 30-50% no uso inicial

**Observações**:
- Valores `contain-intrinsic-size` são estimativas, não precisam ser exatos
- Browser corrige a altura após primeiro render do elemento
- Funciona automaticamente com scroll (lazy rendering nativo)
