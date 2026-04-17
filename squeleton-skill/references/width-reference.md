# Width Reference

## Width - Percentual

Padrão: `{breakpoint-}w-{valor}`

### 10% até 100% (intervalo de 5%)

```
w-10, w-15, w-20, w-25, w-30
w-35, w-40, w-45, w-50, w-55
w-60, w-65, w-70, w-75, w-80
w-85, w-90, w-95, w-100
```

### Responsivos

```
md-w-{valor} (mesmo padrão)
sm-w-{valor} (mesmo padrão)
xs-w-{valor} (mesmo padrão)
```

## Width - Auto

```
w-auto
md-w-auto
sm-w-auto
xs-w-auto
```

## Width - Pixels

Padrão: `{breakpoint-}w-{valor}px`

> Todas as classes `w-*px` incluem `max-width: 100%` automaticamente, garantindo que o elemento nunca exceda o container pai - evitando overflow horizontal em telas menores.

### 5px até 100px (intervalo de 5px)

```
w-5px, w-10px, w-15px, w-20px, w-25px
w-30px, w-35px, w-40px, w-45px, w-50px
w-55px, w-60px, w-65px, w-70px, w-75px
w-80px, w-85px, w-90px, w-95px, w-100px
```

### 100px até 300px (intervalo de 10px)

```
w-110px, w-120px, w-130px, w-140px, w-150px
w-160px, w-170px, w-180px, w-190px, w-200px
w-210px, w-220px, w-230px, w-240px, w-250px
w-260px, w-270px, w-280px, w-290px, w-300px
```

### 300px até 900px (intervalo de 50px)

```
w-350px, w-400px, w-450px, w-500px
w-550px, w-600px, w-650px, w-700px
w-750px, w-800px, w-850px, w-900px
```

### Responsivos

```
md-w-{valor}px (mesmo padrão)
sm-w-{valor}px (mesmo padrão)
xs-w-{valor}px (mesmo padrão)
```

## Exemplos de Uso

```html
<!-- Largura percentual -->
<div class="w-50">50% de largura</div>

<!-- Largura responsiva -->
<div class="xs-w-100 md-w-50">100% no mobile, 50% no desktop</div>

<!-- Largura fixa -->
<div class="w-300px">300px de largura</div>

<!-- Modal com largura controlada -->
<div class="w-600px">Conteudo do modal</div>

<!-- Sidebar fixa -->
<aside class="w-250px f-shrink-0">Sidebar</aside>
```
