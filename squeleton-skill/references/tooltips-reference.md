# Tooltips Reference (Balloon.css)

Tooltips em CSS puro, sem JavaScript.

Documentação oficial: https://kazzkiq.github.io/balloon.css

## Uso Básico

```html
<button aria-label="Texto do tooltip" data-balloon-pos="up">
    Hover aqui
</button>
```

## Posições Disponíveis

| Posição | Atributo |
|---------|----------|
| Acima | `data-balloon-pos="up"` |
| Abaixo | `data-balloon-pos="down"` |
| Esquerda | `data-balloon-pos="left"` |
| Direita | `data-balloon-pos="right"` |
| Acima-Esquerda | `data-balloon-pos="up-left"` |
| Acima-Direita | `data-balloon-pos="up-right"` |
| Abaixo-Esquerda | `data-balloon-pos="down-left"` |
| Abaixo-Direita | `data-balloon-pos="down-right"` |

## Atributos Opcionais

| Atributo | Descrição |
|----------|-----------|
| `data-balloon-visible` | Tooltip sempre visível |
| `data-balloon-nofocus` | Não mostra no focus |
| `data-balloon-break` | Permite quebra de linha |
| `data-balloon-length` | Controla largura (small, medium, large, xlarge, fit) |

## Exemplos

```html
<!-- Tooltip básico acima -->
<span aria-label="Informação útil" data-balloon-pos="up">
    <span class="iccon-info-1"></span>
</span>

<!-- Tooltip à direita -->
<button aria-label="Clique para salvar" data-balloon-pos="right">
    Salvar
</button>

<!-- Tooltip sempre visível -->
<div aria-label="Novo!" data-balloon-pos="up" data-balloon-visible>
    Recurso
</div>

<!-- Tooltip com quebra de linha -->
<span aria-label="Linha 1
Linha 2" data-balloon-pos="down" data-balloon-break>
    Multi-linha
</span>

<!-- Tooltip largo -->
<span aria-label="Texto muito longo que precisa de mais espaço" data-balloon-pos="up" data-balloon-length="large">
    Hover
</span>
```

## Tamanhos (data-balloon-length)

```html
<!-- Pequeno (80px) -->
<span data-balloon-length="small">...</span>

<!-- Médio (150px) -->
<span data-balloon-length="medium">...</span>

<!-- Grande (260px) -->
<span data-balloon-length="large">...</span>

<!-- Extra grande (380px) -->
<span data-balloon-length="xlarge">...</span>

<!-- Ajusta ao conteúdo -->
<span data-balloon-length="fit">...</span>
```
