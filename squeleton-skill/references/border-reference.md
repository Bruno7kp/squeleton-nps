# Border Reference

## Sufixos de Direção

| Sufixo | Aplicação |
|--------|-----------|
| `-all` | Todos os lados |
| `-t` | Top |
| `-b` | Bottom |
| `-l` | Left |
| `-r` | Right |
| `-tb` | Top + Bottom |
| `-lr` | Left + Right |

---

## Border (aplicar borda)

```
border-all    → border: 1px solid
border-t      → border-top: 1px solid
border-b      → border-bottom: 1px solid
border-l      → border-left: 1px solid
border-r      → border-right: 1px solid
border-tb     → border-top + border-bottom: 1px solid
border-lr     → border-left + border-right: 1px solid
```

## Border Width

```
border-w-1    → 1px
border-w-2    → 2px
border-w-3    → 3px
border-w-4    → 4px
border-w-5    → 5px
```

## Border Style

```
border-solid
border-dashed
border-dotted
border-none
border-hidden
border-transparent
```

## Remover Border

```
border-0-t, border-0-b, border-0-l, border-0-r, border-0-all
```

Responsivos:
```
{xs|sm|md}-border-0-{t|b|l|r|all}
```

---

## Border Radius

### Todos os cantos (0-20px, de 1 em 1)

```
border-rd-{0-20}    → 0px até 20px
```

Responsivos:
```
{xs|sm|md}-border-rd-{0-20}
```

### Circular

```
border-rd-50     → 50%
border-rd-100    → 50%
```

Responsivos:
```
{xs|sm|md}-border-rd-100
```

### Cantos superiores (valores pares: 0, 2, 4... 20)

```
border-rd-{valor}-t    → border-top-left-radius + border-top-right-radius
```

Responsivos:
```
{xs|sm|md}-border-rd-{0|2|4|6|8|10|12|14|16|18|20}-t
```

### Cantos inferiores (valores pares: 0, 2, 4... 20)

```
border-rd-{valor}-b    → border-bottom-left-radius + border-bottom-right-radius
```

Responsivos:
```
{xs|sm|md}-border-rd-{0|2|4|6|8|10|12|14|16|18|20}-b
```

---

## Opacity

```
opacity-1    → 0.15
opacity-2    → 0.25
opacity-3    → 0.35
opacity-4    → 0.45
opacity-5    → 0.55
opacity-6    → 0.65
opacity-7    → 0.75
opacity-8    → 0.85
opacity-9    → 0.9
```

## Opacity Background (overlay absoluto)

```
opacity-bg-0     → opacity: 1 (opaco)
opacity-bg-1     → opacity: 0.9
opacity-bg-2     → opacity: 0.85
opacity-bg-3     → opacity: 0.75
opacity-bg-4     → opacity: 0.65
opacity-bg-5     → opacity: 0.55
opacity-bg-6     → opacity: 0.45
opacity-bg-7     → opacity: 0.35
opacity-bg-8     → opacity: 0.25
opacity-bg-9     → opacity: 0.15
opacity-bg-10    → opacity: 0 (transparente)
```

---

## Z-Index

```
z-index-0
z-index-1
z-index-2
z-index-3
z-index-4
z-index-5
z-index-111
z-index-1111
z-index-minus-2    → -2
```

---

## Exemplos de Uso

```html
<!-- Card com borda e radius -->
<div class="border-all border-rd-8 p-20-all">
    Card content
</div>

<!-- Borda inferior destacada -->
<div class="border-b border-w-2">
    Seção
</div>

<!-- Avatar circular -->
<img src="avatar.jpg" class="border-rd-50 w-50px h-50px">

<!-- Tab com radius só em cima -->
<div class="border-rd-8-t border-all border-0-b">
    Tab ativa
</div>

<!-- Border radius responsivo: 12px desktop, 8px tablet, 4px mobile -->
<div class="border-rd-12 md-border-rd-8 xs-border-rd-4">
    Card responsivo
</div>

<!-- Overlay escuro sobre imagem -->
<div class="ps-relative">
    <img src="bg.jpg">
    <div class="opacity-bg-3 bg-black"></div>
</div>
```
