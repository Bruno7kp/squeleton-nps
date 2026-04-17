# Display Reference

Padrão: `{breakpoint-}d-{valor}`

## Classes Base - Global

```
d-none
d-block
d-inline
d-inline-block
d-flex
d-inline-flex
d-grid
d-contents
d-table
d-table-cell
```

## Responsivo - MD (≤1199px)

```
md-d-none
md-d-block
md-d-inline
md-d-inline-block
md-d-flex
md-d-inline-flex
md-d-grid
md-d-contents
md-d-table
md-d-table-cell
```

## Responsivo - SM (≤991px)

```
sm-d-none
sm-d-block
sm-d-inline
sm-d-inline-block
sm-d-flex
sm-d-inline-flex
sm-d-grid
sm-d-contents
sm-d-table
sm-d-table-cell
```

## Responsivo - XS (≤639px)

```
xs-d-none
xs-d-block
xs-d-inline
xs-d-inline-block
xs-d-flex
xs-d-inline-flex
xs-d-grid
xs-d-contents
xs-d-table
xs-d-table-cell
```

## Overflow

```
ov-hidden
ov-visible
ov-auto
ov-scroll
xs-ov-hidden, xs-ov-auto, xs-ov-scroll
sm-ov-hidden, sm-ov-auto, sm-ov-scroll
md-ov-hidden, md-ov-auto, md-ov-scroll
```

## Position

```
ps-relative
ps-absolute
ps-fixed
ps-sticky
ps-static
md-ps-relative, md-ps-absolute, md-ps-fixed, md-ps-sticky, md-ps-static
sm-ps-relative, sm-ps-absolute, sm-ps-fixed, sm-ps-sticky, sm-ps-static
xs-ps-relative, xs-ps-absolute, xs-ps-fixed, xs-ps-sticky, xs-ps-static
```

## Visibility

```
hidden-print (oculto na impressão)
```

## Exemplos de Uso

```html
<!-- Ocultar no mobile, mostrar no desktop -->
<div class="xs-d-none md-d-block">Visível apenas no desktop</div>

<!-- Mostrar no mobile, ocultar no desktop -->
<div class="d-block md-d-none">Visível apenas no mobile</div>

<!-- Flex no desktop, block no mobile -->
<div class="xs-d-block md-d-flex">Layout adaptativo</div>
```
