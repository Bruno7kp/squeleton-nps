# Height Reference

## Height - Pixels

Padrao: `{breakpoint-}h-{valor}px`

### 5px ate 100px (intervalo de 5px)

```
h-5px, h-10px, h-15px, h-20px, h-25px
h-30px, h-35px, h-40px, h-50px
h-55px, h-60px, h-65px, h-70px, h-75px
h-80px, h-85px, h-90px, h-95px, h-100px
```

### 100px ate 300px (intervalo de 10px)

```
h-110px, h-120px, h-130px, h-140px, h-150px
h-160px, h-170px, h-180px, h-190px, h-200px
h-210px, h-220px, h-230px, h-240px, h-250px
h-260px, h-270px, h-280px, h-290px, h-300px
```

### 300px ate 900px (intervalo de 50px)

```
h-350px, h-400px, h-450px, h-500px
h-550px, h-600px, h-650px, h-700px
h-750px, h-800px, h-850px, h-900px
```

### Responsivos

```
md-h-{valor}px (mesmo padrao)
sm-h-{valor}px (mesmo padrao)
xs-h-{valor}px (mesmo padrao)
```

## Height - Percentual

Padrao: `{breakpoint-}h-{valor}` (sem unidade = porcentagem)

```
h-50, h-100
```

### Responsivos

```
md-h-{valor} (mesmo padrao)
sm-h-{valor} (mesmo padrao)
xs-h-{valor} (mesmo padrao)
```

## Height - Viewport

```
h-100vh
md-h-100vh
sm-h-100vh
xs-h-100vh

h-100dvh
md-h-100dvh
sm-h-100dvh
xs-h-100dvh
```

## Height - Auto

```
h-auto
md-h-auto
sm-h-auto
xs-h-auto
```

## Exemplos de Uso

```html
<!-- Altura fixa -->
<div class="h-300px">300px de altura</div>

<!-- Altura percentual -->
<div class="h-100">100% de altura</div>

<!-- Altura viewport -->
<div class="h-100vh">100% da viewport</div>

<!-- Altura responsiva -->
<div class="h-400px md-h-300px sm-h-200px xs-h-100px">
  Altura adaptativa por breakpoint
</div>

<!-- Container full height -->
<main class="h-100vh d-flex f-col">
  <header class="h-60px">Header</header>
  <section class="f-grow-1">Conteudo</section>
  <footer class="h-80px">Footer</footer>
</main>

<!-- Card com altura minima -->
<div class="h-200px">Card com altura minima de 200px</div>
```
