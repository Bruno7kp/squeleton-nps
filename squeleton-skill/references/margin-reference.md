# Margin Reference

Padrão: `{breakpoint-}m-{valor}-{direção}`

## Direções

| Sufixo | Aplicação |
|--------|-----------|
| `-all` | Todos os lados |
| `-t` | Top |
| `-b` | Bottom |
| `-l` | Left |
| `-r` | Right |
| `-tb` | Top + Bottom |
| `-lr` | Left + Right |

## Margin Auto (centralização)

```
m-auto-lr (margin-left: auto, margin-right: auto - centraliza horizontalmente)
m-auto-l (margin-left: auto)
m-auto-r (margin-right: auto)
m-auto-t (margin-top: auto)
m-auto-b (margin-bottom: auto)
```

## Valores em Pixels (0-100, intervalo de 5)

```
m-0-all, m-0-t, m-0-b, m-0-l, m-0-r, m-0-tb, m-0-lr
m-5-all, m-5-t, m-5-b, m-5-l, m-5-r, m-5-tb, m-5-lr
m-10-all, m-10-t, m-10-b, m-10-l, m-10-r, m-10-tb, m-10-lr
m-15-all, m-15-t, m-15-b, m-15-l, m-15-r, m-15-tb, m-15-lr
m-20-all, m-20-t, m-20-b, m-20-l, m-20-r, m-20-tb, m-20-lr
m-25-all, m-25-t, m-25-b, m-25-l, m-25-r, m-25-tb, m-25-lr
m-30-all, m-30-t, m-30-b, m-30-l, m-30-r, m-30-tb, m-30-lr
m-35-all, m-35-t, m-35-b, m-35-l, m-35-r, m-35-tb, m-35-lr
m-40-all, m-40-t, m-40-b, m-40-l, m-40-r, m-40-tb, m-40-lr
m-45-all, m-45-t, m-45-b, m-45-l, m-45-r, m-45-tb, m-45-lr
m-50-all, m-50-t, m-50-b, m-50-l, m-50-r, m-50-tb, m-50-lr
m-55-all, m-55-t, m-55-b, m-55-l, m-55-r, m-55-tb, m-55-lr
m-60-all, m-60-t, m-60-b, m-60-l, m-60-r, m-60-tb, m-60-lr
m-65-all, m-65-t, m-65-b, m-65-l, m-65-r, m-65-tb, m-65-lr
m-70-all, m-70-t, m-70-b, m-70-l, m-70-r, m-70-tb, m-70-lr
m-75-all, m-75-t, m-75-b, m-75-l, m-75-r, m-75-tb, m-75-lr
m-80-all, m-80-t, m-80-b, m-80-l, m-80-r, m-80-tb, m-80-lr
m-85-all, m-85-t, m-85-b, m-85-l, m-85-r, m-85-tb, m-85-lr
m-90-all, m-90-t, m-90-b, m-90-l, m-90-r, m-90-tb, m-90-lr
m-95-all, m-95-t, m-95-b, m-95-l, m-95-r, m-95-tb, m-95-lr
m-100-all, m-100-t, m-100-b, m-100-l, m-100-r, m-100-tb, m-100-lr
```

## Versões Responsivas

**Todas as classes de pixels estão disponíveis com prefixos de breakpoint:**

```
md-m-{valor}-{direção}  (≤1199px - max-width)
sm-m-{valor}-{direção}  (≤991px - max-width)
xs-m-{valor}-{direção}  (≤639px - max-width)
```

**Exemplos:**
```
md-m-20-all    (margin: 20px em ≤1199px)
sm-m-15-tb     (margin top/bottom: 15px em ≤991px)
xs-m-10-all    (margin: 10px em ≤639px)
md-m-0-b       (margin-bottom: 0 em ≤1199px)
md-m-auto-lr   (margin-left/right: auto em ≤1199px - centralização)
```

**Padrão de uso:**
- Use a classe base para o comportamento global
- Adicione breakpoints para sobrescrever em tamanhos específicos
- Lembre-se: Squeleton usa `@media (max-width)` para utilitários