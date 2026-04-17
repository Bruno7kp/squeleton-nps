# Typography Reference

## Text Align

```
text-left
text-center
text-right
text-justify
```

Responsivos: `{xs|sm|md}-text-*`

## Text Transform

```
text-lowercase
text-uppercase
text-capitalize
```

## Text Decoration

```
underline
overline
line-through
color-inherit
```

## Font Weight

```
fw-300
fw-400
fw-500
fw-600
fw-700
fw-800
fw-900
```

## Font Size - Escala Numérica (fs-1 a fs-16)

Escala completa de tamanhos de fonte. Classes fs-1 a fs-6 são fixas, fs-7 a fs-16 usam `clamp()` para tipografia fluida.

```
fs-1   (10px - fixo)         Micro labels
fs-2   (11px - fixo)         Badges, captions
fs-3   (12px - fixo)         Small text
fs-4   (13px - fixo)         Secondary text
fs-5   (14px - fixo)         Body small
fs-6   (15px - fixo)         Body alt
fs-7   (15px → 16px)         Body padrão
fs-8   (16px → 17px)         Body large
fs-9   (17px → 18px)         Lead text
fs-10  (18px → 20px)         Subtítulo
fs-11  (21px → 24px)         Título pequeno
fs-12  (24px → 28px)         Título médio
fs-13  (28px → 32px)         Título grande
fs-14  (34px → 40px)         Headline
fs-15  (40px → 48px)         Hero
fs-16  (52px → 64px)         Display
```

Responsivos: `{xs|sm|md}-fs-{1-16}`

**Exemplos**:
```html
<span class="fs-1">Badge</span>
<p class="fs-7">Texto padrão do corpo</p>
<h2 class="fs-12">Título de seção</h2>
<h1 class="fs-16 xs-fs-14">Hero headline</h1>
```

## Font Size - Redução

```
minus-10 (font-size: 90%)
minus-15 (font-size: 85%)
minus-20 (font-size: 80%)
minus-25 (font-size: 75%)
minus-30 (font-size: 70%)
minus-35 (font-size: 65%)
minus-40 (font-size: 60%)
minus-45 (font-size: 55%)
minus-50 (font-size: 50%)
```

## Font Size - Ampliação

```
more-10 (font-size: 110%)
more-15 (font-size: 115%)
more-20 (font-size: 120%)
more-25 (font-size: 125%)
more-30 (font-size: 130%)
more-35 (font-size: 135%)
more-40 (font-size: 140%)
more-45 (font-size: 145%)
more-50 (font-size: 150%)
```

## Letter Spacing - Positivo

```
ls-0 (0)
ls-1 (1px)
ls-2 (2px)
ls-3 (3px)
ls-4 (4px)
ls-5 (5px)
```

## Letter Spacing - Negativo

```
ls-minus-0-2 (-0.2px)
ls-minus-0-5 (-0.5px)
ls-minus-1 (-1px)
ls-minus-2 (-2px)
ls-minus-3 (-3px)
```

## Line Height

```
lh-0-7 (0.7)
lh-0-8 (0.8)
lh-0-9 (0.9)
lh-1-0 (1.0)
lh-1-1 (1.1)
lh-1-2 (1.2)
lh-1-3 (1.3)
lh-1-4 (1.4)
lh-1-5 (1.5)
lh-1-6 (1.6)
lh-1-7 (1.7)
lh-1-8 (1.8)
lh-1-9 (1.9)
```

Responsivos: `{xs|sm|md}-lh-{valor}` (mesmos valores acima)

## White Space

```
text-nowrap (white-space: nowrap)
word-wrap (word-wrap: break-word)
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

## Exemplos de Uso

```html
<!-- Título com peso e espaçamento -->
<h1 class="fw-700 ls-minus-1">Título Principal</h1>

<!-- Texto menor com line-height ajustado -->
<p class="minus-10 lh-1-6">Texto secundário</p>

<!-- Label uppercase com espaçamento -->
<span class="text-uppercase ls-2 fw-600 minus-20">Label</span>

<!-- Preço grande -->
<span class="more-50 fw-700">R$ 99,90</span>

<!-- Alinhamento responsivo -->
<p class="xs-text-center md-text-left">
    Centralizado no mobile, esquerda no desktop
</p>
```
