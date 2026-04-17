# Toastify Reference

Biblioteca para notificações toast customizáveis. Mensagens temporárias que aparecem de forma discreta e desaparecem automaticamente.

## Uso Básico

```javascript
Toastify({
    text: "Mensagem do toast",
    duration: 3000
}).showToast();
```

## Opções Principais

| Opção | Tipo | Descrição |
|-------|------|-----------|
| `text` | string | Texto a ser exibido |
| `duration` | number | Duração em ms (3000 = 3s), -1 para permanente |
| `close` | boolean | Mostrar botão de fechar |
| `gravity` | string | Posição vertical: "top" ou "bottom" |
| `position` | string | Posição horizontal: "left", "center" ou "right" |
| `destination` | string | URL para redirecionar ao clicar |
| `newWindow` | boolean | Abrir link em nova janela |
| `stopOnFocus` | boolean | Pausar contagem ao passar o mouse |
| `onClick` | function | Callback ao clicar no toast |
| `style` | object | Estilos CSS personalizados |
| `className` | string | Classe CSS adicional |

## Exemplos

### Toast de Sucesso
```javascript
function showSuccessToast() {
    Toastify({
        text: "Operação realizada com sucesso!",
        duration: 3000,
        close: true,
        gravity: "top",
        position: "right",
        style: {
            background: "linear-gradient(to right, #00b09b, #96c93d)",
        }
    }).showToast();
}
```

### Toast de Erro
```javascript
function showErrorToast() {
    Toastify({
        text: "Erro ao processar solicitação",
        duration: 3000,
        close: true,
        gravity: "bottom",
        position: "center",
        style: {
            background: "linear-gradient(to right, #ff5f6d, #ffc371)",
        }
    }).showToast();
}
```

### Toast com Link
```javascript
function showInfoToast() {
    Toastify({
        text: "Clique para mais informações",
        duration: 5000,
        destination: "https://exemplo.com",
        newWindow: true,
        close: true,
        gravity: "bottom",
        position: "left",
        stopOnFocus: true,
        style: {
            background: "linear-gradient(to right, #4b6cb7, #182848)",
        },
        onClick: function () {
            console.log("Toast clicado");
        }
    }).showToast();
}
```

## Exemplo Completo com HTML

```html
<button onclick="showSuccessToast()" class="btn alert-success m-10-r">
    Sucesso
</button>
<button onclick="showErrorToast()" class="btn alert-danger m-10-r">
    Erro
</button>
<button onclick="showInfoToast()" class="btn alert-info">
    Info
</button>

<script>
// Toastify é carregado automaticamente pelo Squeleton

function showSuccessToast() {
    Toastify({
        text: "Operação realizada com sucesso!",
        duration: 3000,
        close: true,
        gravity: "top",
        position: "right",
        style: {
            background: "linear-gradient(to right, #00b09b, #96c93d)",
        }
    }).showToast();
}

function showErrorToast() {
    Toastify({
        text: "Erro ao processar solicitação",
        duration: 3000,
        close: true,
        gravity: "bottom",
        position: "center",
        style: {
            background: "linear-gradient(to right, #ff5f6d, #ffc371)",
        }
    }).showToast();
}

function showInfoToast() {
    Toastify({
        text: "Clique para mais informações",
        duration: 5000,
        destination: "https://exemplo.com",
        newWindow: true,
        close: true,
        gravity: "bottom",
        position: "left",
        stopOnFocus: true,
        style: {
            background: "linear-gradient(to right, #4b6cb7, #182848)",
        }
    }).showToast();
}
</script>
```

## Posições

| gravity | position | Resultado |
|---------|----------|-----------|
| top | left | Canto superior esquerdo |
| top | center | Centro superior |
| top | right | Canto superior direito |
| bottom | left | Canto inferior esquerdo |
| bottom | center | Centro inferior |
| bottom | right | Canto inferior direito |

## Estilos Comuns

### Cores de gradiente sugeridas

```javascript
// Sucesso (verde)
background: "linear-gradient(to right, #00b09b, #96c93d)"

// Erro (vermelho/laranja)
background: "linear-gradient(to right, #ff5f6d, #ffc371)"

// Info (azul)
background: "linear-gradient(to right, #4b6cb7, #182848)"

// Warning (amarelo)
background: "linear-gradient(to right, #f7971e, #ffd200)"

// Neutro (cinza)
background: "linear-gradient(to right, #bdc3c7, #2c3e50)"
```

## Casos de Uso

- **Confirmação de ação**: "Item adicionado ao carrinho"
- **Erros de validação**: "Preencha todos os campos obrigatórios"
- **Notificações**: "Nova mensagem recebida"
- **Status de operação**: "Salvando...", "Salvo com sucesso!"
- **Alertas informativos**: "Sua sessão expira em 5 minutos"
