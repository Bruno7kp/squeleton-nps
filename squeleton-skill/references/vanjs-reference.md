# VanJS Reference

Framework reativo minimalista (~1KB). Entrega recursos fundamentais para desenvolvimento web reativo: templates DOM, estado, vinculação de estado, derivação de estado e efeitos.

## Funções Principais do VanJS

| Função | Descrição |
|--------|-----------|
| `van.tags` | Cria elementos HTML de forma programática (hyperscript) |
| `van.add` | Adiciona componentes VanJS ao DOM |
| `van.state` | Cria estado reativo que atualiza o DOM automaticamente |
| `van.derive` | Executa efeitos colaterais quando estados mudam |

## Funções Extendidas do Squeleton

| Função | Descrição |
|--------|-----------|
| `persistentItem(key, defaultValue)` | Estado que persiste no localStorage |
| `persistentGroup(prefix, defaults)` | Grupo de estados persistentes relacionados |

## Exemplo 1: Contador Simples (Sem Persistência)

```html
<div id="box-counter-1"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const { span, button } = van.tags
    const boxCounter1 = document.getElementById('box-counter-1');

    const Counter1 = () => {
        const counterState = van.state(0)
        return span(
            "❤️ ", counterState, " ",
            button({ onclick: () => ++counterState.val }, "👍"),
            button({ onclick: () => --counterState.val }, "👎"),
        )
    }

    van.add(boxCounter1, Counter1())
});
</script>
```

## Exemplo 2: Contador com Persistência

O valor sobrevive a recarregamentos de página.

```html
<div id="box-counter-2"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const { span, button } = van.tags
    const boxCounter2 = document.getElementById('box-counter-2');

    const Counter2 = () => {
        const counterPersistent = persistentItem("counter_persistent", 0)
        return span(
            "❤️ ", counterPersistent, " ",
            button({ onclick: () => ++counterPersistent.val }, "👍"),
            button({ onclick: () => --counterPersistent.val }, "👎"),
        )
    }

    van.add(boxCounter2, Counter2())
});
</script>
```

## Exemplo 3: Grupo de Estados Persistentes

Múltiplos estados relacionados com prefixo comum.

```html
<div id="box-counter-3"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const { span, button } = van.tags
    const boxCounter3 = document.getElementById('box-counter-3');

    const Counter3 = () => {
        const counters = persistentGroup("counters", {
            likes: 0,
            deslikes: 0
        })

        return span(
            "👍 ", counters.likes, " ",
            button({ onclick: () => ++counters.likes.val }, "➕"),
            " ",
            "👎 ", counters.deslikes, " ",
            button({ onclick: () => ++counters.deslikes.val }, "➕"),
            " ",
            button({ onclick: () => counters.clearAll() }, "🗑️")
        )
    }

    van.add(boxCounter3, Counter3())
});
</script>
```

## API do persistentItem

```javascript
// Criar estado persistente
const contador = persistentItem("meu_contador", 0)

// Ler valor
console.log(contador.val)

// Alterar valor (persiste automaticamente)
contador.val = 10
++contador.val

// Limpar (remove do storage e reseta para default)
contador.clear()
```

## API do persistentGroup

```javascript
// Criar grupo de estados
const config = persistentGroup("config", {
    tema: "light",
    idioma: "pt",
    notificacoes: true
})

// Acessar estados individuais
config.tema.val = "dark"
config.idioma.val = "en"

// Limpar todos os estados do grupo
config.clearAll()
```

## Criando Elementos com van.tags

```javascript
const { div, h1, p, button, input, ul, li, a, img } = van.tags

// Elemento simples
const titulo = h1("Olá Mundo")

// Elemento com atributos
const botao = button({
    class: "btn alert-success",
    onclick: () => alert("Clicado!")
}, "Clique aqui")

// Elemento com filhos
const card = div({ class: "card p-20-all" },
    h1("Título"),
    p("Descrição do card"),
    button({ onclick: () => {} }, "Ação")
)

// Lista dinâmica
const itens = ["Item 1", "Item 2", "Item 3"]
const lista = ul(
    itens.map(item => li(item))
)
```

## Reatividade com van.state

```javascript
const { div, input, p } = van.tags

const App = () => {
    const nome = van.state("")

    return div(
        input({
            type: "text",
            placeholder: "Digite seu nome",
            oninput: (e) => nome.val = e.target.value
        }),
        p("Olá, ", nome, "!")
    )
}

van.add(document.body, App())
```

## Derivação com van.derive

```javascript
const { div, p } = van.tags

const App = () => {
    const quantidade = van.state(0)
    const preco = van.state(10)

    // Valor derivado que atualiza automaticamente
    const total = van.derive(() => quantidade.val * preco.val)

    return div(
        p("Quantidade: ", quantidade),
        p("Preço: R$ ", preco),
        p("Total: R$ ", total)
    )
}
```
