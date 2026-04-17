# HTMX Reference

Biblioteca para requisições AJAX declarativas via atributos HTML. Permite atualizar partes específicas da página sem recarregar, tornando a navegação mais rápida e fluida.

## Exemplo: Click to Load

```html
<div id="blog" class="row">
    <div class="c-xs-12 c-md-6">
        <h4>Introdução ao Squeleton</h4>
        <p>Conheça as principais novidades...</p>
    </div>
    <div class="c-xs-12 c-md-6">
        <h4>Sistemas de Grid Responsivos</h4>
        <p>Como utilizar o sistema de grid...</p>
    </div>
</div>

<!-- Botão com HTMX para carregar mais artigos -->
<button class="btn alert-info m-20-t"
        hx-get="/api/artigos?page=2"
        hx-target="#blog"
        hx-swap="beforeend"
        hx-trigger="click">
    Carregar Mais Artigos
</button>
```

## Atributos Principais

| Atributo | Descrição |
|----------|-----------|
| `hx-get` | URL para buscar conteúdo via GET |
| `hx-post` | URL para enviar dados via POST |
| `hx-put` | URL para enviar dados via PUT |
| `hx-delete` | URL para enviar dados via DELETE |
| `hx-target` | Seletor do elemento que receberá o conteúdo |
| `hx-swap` | Como o conteúdo será inserido |
| `hx-trigger` | Evento que dispara a requisição |
| `hx-indicator` | Elemento exibido durante o carregamento |

## Valores de hx-swap

| Valor | Descrição |
|-------|-----------|
| `innerHTML` | Substitui o conteúdo interno (padrão) |
| `outerHTML` | Substitui o elemento inteiro |
| `beforeend` | Insere no final do elemento |
| `afterbegin` | Insere no início do elemento |
| `beforebegin` | Insere antes do elemento |
| `afterend` | Insere depois do elemento |
| `none` | Não faz swap (útil para side effects) |

## Valores de hx-trigger

| Valor | Descrição |
|-------|-----------|
| `click` | Ao clicar (padrão para botões/links) |
| `change` | Ao mudar valor (inputs) |
| `submit` | Ao submeter formulário |
| `load` | Ao carregar elemento |
| `revealed` | Quando elemento entra no viewport |
| `intersect` | Quando elemento cruza threshold |
| `every Ns` | A cada N segundos |

## Exemplos Práticos

### Formulário com POST
```html
<form class="f-col f-gap-15" hx-post="/api/contato" hx-target="#resultado" hx-swap="innerHTML">
    <input class="form-control" type="text" name="nome" placeholder="Nome">
    <input class="form-control" type="email" name="email" placeholder="Email">
    <button class="btn alert-success" type="submit">Enviar</button>
</form>
<div id="resultado" class="m-15-t"></div>
```

### Busca em tempo real
```html
<input class="form-control"
       type="search"
       name="q"
       placeholder="Buscar..."
       hx-get="/api/busca"
       hx-target="#resultados"
       hx-trigger="keyup changed delay:300ms">
<div id="resultados" class="m-15-t"></div>
```

### Infinite Scroll
```html
<div id="feed" class="row gap-15">
    <!-- Posts carregados -->
</div>
<div class="t-center p-20-all"
     hx-get="/api/posts?page=2"
     hx-target="#feed"
     hx-swap="beforeend"
     hx-trigger="revealed">
    <span class="t-muted">Carregando mais...</span>
</div>
```

### Com Indicador de Loading
```html
<button class="btn alert-info"
        hx-get="/api/dados"
        hx-target="#conteudo"
        hx-indicator="#loading">
    Carregar
</button>
<span id="loading" class="htmx-indicator t-muted m-10-l">Carregando...</span>
<div id="conteudo" class="m-15-t"></div>
```

## Atributos Adicionais

| Atributo | Descrição |
|----------|-----------|
| `hx-confirm` | Exibe confirmação antes de executar |
| `hx-disable` | Desabilita elemento durante requisição |
| `hx-include` | Inclui valores de outros inputs |
| `hx-params` | Filtra quais parâmetros enviar |
| `hx-vals` | Adiciona valores extras à requisição |
| `hx-headers` | Adiciona headers customizados |

## Eventos JavaScript

```javascript
// Antes da requisição
document.body.addEventListener('htmx:beforeRequest', function(evt) {
    console.log('Iniciando requisição...');
});

// Após resposta
document.body.addEventListener('htmx:afterSwap', function(evt) {
    console.log('Conteúdo atualizado');
});

// Em caso de erro
document.body.addEventListener('htmx:responseError', function(evt) {
    console.error('Erro na requisição');
});
```
