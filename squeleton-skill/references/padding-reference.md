# Padding Reference

Padrão: `{breakpoint-}p-{valor}-{direção}`

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

## Valores em Pixels (0-100, intervalo de 5)

```
p-0-all, p-0-t, p-0-b, p-0-l, p-0-r, p-0-tb, p-0-lr
p-5-all, p-5-t, p-5-b, p-5-l, p-5-r, p-5-tb, p-5-lr
p-10-all, p-10-t, p-10-b, p-10-l, p-10-r, p-10-tb, p-10-lr
p-15-all, p-15-t, p-15-b, p-15-l, p-15-r, p-15-tb, p-15-lr
p-20-all, p-20-t, p-20-b, p-20-l, p-20-r, p-20-tb, p-20-lr
p-25-all, p-25-t, p-25-b, p-25-l, p-25-r, p-25-tb, p-25-lr
p-30-all, p-30-t, p-30-b, p-30-l, p-30-r, p-30-tb, p-30-lr
p-35-all, p-35-t, p-35-b, p-35-l, p-35-r, p-35-tb, p-35-lr
p-40-all, p-40-t, p-40-b, p-40-l, p-40-r, p-40-tb, p-40-lr
p-45-all, p-45-t, p-45-b, p-45-l, p-45-r, p-45-tb, p-45-lr
p-50-all, p-50-t, p-50-b, p-50-l, p-50-r, p-50-tb, p-50-lr
p-55-all, p-55-t, p-55-b, p-55-l, p-55-r, p-55-tb, p-55-lr
p-60-all, p-60-t, p-60-b, p-60-l, p-60-r, p-60-tb, p-60-lr
p-65-all, p-65-t, p-65-b, p-65-l, p-65-r, p-65-tb, p-65-lr
p-70-all, p-70-t, p-70-b, p-70-l, p-70-r, p-70-tb, p-70-lr
p-75-all, p-75-t, p-75-b, p-75-l, p-75-r, p-75-tb, p-75-lr
p-80-all, p-80-t, p-80-b, p-80-l, p-80-r, p-80-tb, p-80-lr
p-85-all, p-85-t, p-85-b, p-85-l, p-85-r, p-85-tb, p-85-lr
p-90-all, p-90-t, p-90-b, p-90-l, p-90-r, p-90-tb, p-90-lr
p-95-all, p-95-t, p-95-b, p-95-l, p-95-r, p-95-tb, p-95-lr
p-100-all, p-100-t, p-100-b, p-100-l, p-100-r, p-100-tb, p-100-lr
```

## Versões Responsivas

**Todas as classes acima estão disponíveis com prefixos de breakpoint:**

```
md-p-{valor}-{direção}  (≤1199px - max-width)
sm-p-{valor}-{direção}  (≤991px - max-width)
xs-p-{valor}-{direção}  (≤639px - max-width)
```

**Exemplos:**
```
md-p-20-all    (padding: 20px em ≤1199px)
sm-p-15-tb     (padding top/bottom: 15px em ≤991px)
xs-p-10-all    (padding: 10px em ≤639px)
md-p-0-b       (padding-bottom: 0 em ≤1199px)
```

**Padrão de uso:**
- Use a classe base para o comportamento global
- Adicione breakpoints para sobrescrever em tamanhos específicos
- Lembre-se: Squeleton usa `@media (max-width)` para utilitários