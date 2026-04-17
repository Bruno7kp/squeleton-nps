# Modal Reference (a11y-dialog)

Modais acessíveis com suporte a teclado e leitores de tela. **Modais com classe `.modal-dialog` já vêm com scroll lock automático** (eventos show/hide).

Documentação oficial: https://a11y-dialog.netlify.app

## Adaptação Squeleton

Os atributos originais `data-a11y-dialog-*` foram renomeados para `data-modal-*`:

| Original | Squeleton |
|----------|-----------|
| `data-a11y-dialog` | `data-modal` |
| `data-a11y-dialog-show` | `data-modal-show` |
| `data-a11y-dialog-hide` | `data-modal-hide` |

## Estrutura Básica

```html
<!-- Trigger -->
<button data-modal-show="meu-modal">Abrir Modal</button>

<!-- Modal -->
<div data-modal="meu-modal" class="modal-dialog" aria-hidden="true">
    <div class="dialog-content">
        <div class="dialog-backdrop" data-modal-hide></div>
        <div class="dialog-inline w-500px">
            <button class="dialog-close" data-modal-hide aria-label="Fechar"></button>
            <div class="modal-popup">
                Conteúdo do modal
            </div>
        </div>
    </div>
</div>
```

## Classes Estruturais

```
modal-dialog (container principal, controla visibilidade)
dialog-content (wrapper do conteúdo)
dialog-backdrop (overlay escuro, clicável para fechar)
dialog-inline (container do conteúdo centralizado)
dialog-close (botão de fechar)
modal-popup (área do conteúdo estilizado)
```

## Atributos

| Atributo | Elemento | Descrição |
|----------|----------|-----------|
| `data-modal="id"` | container | Define o ID do modal |
| `data-modal-show="id"` | trigger | Abre o modal com este ID |
| `data-modal-hide` | elemento | Fecha o modal ao clicar |
| `aria-hidden="true"` | container | Estado inicial (fechado) |
| `aria-label="Fechar"` | botão close | Acessibilidade |

## Modal Completo com Estilos

```html
<button data-modal-show="modal-exemplo" class="btn">Abrir Modal</button>

<div data-modal="modal-exemplo" class="modal-dialog" aria-hidden="true" aria-modal="true" role="dialog" tabindex="-1">
    <div class="dialog-content">
        <div class="dialog-backdrop" data-modal-hide></div>
        <div class="dialog-inline w-500px">
            <button class="dialog-close" data-modal-hide aria-label="Fechar">
                <span class="iccon-close-1"></span>
            </button>
            <div class="modal-popup border-rd-10 p-30-all">
                <h3 class="m-15-b">Título do Modal</h3>
                <p>Conteúdo do modal aqui...</p>
                <div class="m-20-t d-flex f-justify-end f-gap-10">
                    <button data-modal-hide>Cancelar</button>
                    <button class="btn-primary">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</div>
```

## Larguras do Modal

Use classes `w-*` no `dialog-inline`:

```html
<div class="dialog-inline w-400px">...</div>  <!-- Pequeno -->
<div class="dialog-inline w-500px">...</div>  <!-- Médio -->
<div class="dialog-inline w-600px">...</div>  <!-- Grande -->
<div class="dialog-inline w-800px">...</div>  <!-- Extra grande -->
```

## Múltiplos Triggers

Vários elementos podem abrir o mesmo modal:

```html
<button data-modal-show="modal-login">Login</button>
<a href="#" data-modal-show="modal-login">Entrar</a>
<span data-modal-show="modal-login" class="iccon-user-1"></span>
```

## Múltiplos Botões de Fechar

```html
<div class="modal-popup">
    <button class="dialog-close" data-modal-hide>×</button>
    <h3>Título</h3>
    <p>Conteúdo...</p>
    <button data-modal-hide>Fechar</button>
    <button data-modal-hide>Cancelar</button>
</div>
```

## Exemplos Práticos

### Modal de Confirmação

```html
<button data-modal-show="confirmar-exclusao">Excluir</button>

<div data-modal="confirmar-exclusao" class="modal-dialog" aria-hidden="true">
    <div class="dialog-content">
        <div class="dialog-backdrop" data-modal-hide></div>
        <div class="dialog-inline w-400px">
            <div class="modal-popup border-rd-10 p-30-all text-center">
                <span class="iccon-exclamation-1 more-50 m-15-b d-block"></span>
                <h4 class="m-10-b">Confirmar exclusão?</h4>
                <p class="opacity-7 m-20-b">Esta ação não pode ser desfeita.</p>
                <div class="d-flex f-justify-center f-gap-10">
                    <button data-modal-hide>Cancelar</button>
                    <button class="btn-danger">Excluir</button>
                </div>
            </div>
        </div>
    </div>
</div>
```

### Modal de Formulário

```html
<button data-modal-show="modal-contato">Contato</button>

<div data-modal="modal-contato" class="modal-dialog" aria-hidden="true">
    <div class="dialog-content">
        <div class="dialog-backdrop" data-modal-hide></div>
        <div class="dialog-inline w-500px">
            <button class="dialog-close" data-modal-hide aria-label="Fechar">
                <span class="iccon-close-1"></span>
            </button>
            <div class="modal-popup border-rd-10 p-30-all">
                <h3 class="m-20-b">Entre em contato</h3>
                <form>
                    <div class="m-15-b">
                        <label>Nome</label>
                        <input type="text" class="w-100">
                    </div>
                    <div class="m-15-b">
                        <label>Email</label>
                        <input type="email" class="w-100">
                    </div>
                    <div class="m-15-b">
                        <label>Mensagem</label>
                        <textarea class="w-100" rows="4"></textarea>
                    </div>
                    <button type="submit" class="w-100">Enviar</button>
                </form>
            </div>
        </div>
    </div>
</div>
```

### Modal de Imagem/Galeria

```html
<img src="thumb.jpg" data-modal-show="modal-imagem" class="cursor-pointer">

<div data-modal="modal-imagem" class="modal-dialog" aria-hidden="true">
    <div class="dialog-content">
        <div class="dialog-backdrop" data-modal-hide></div>
        <div class="dialog-inline w-900px">
            <button class="dialog-close" data-modal-hide aria-label="Fechar">
                <span class="iccon-close-1"></span>
            </button>
            <div class="modal-popup">
                <img src="imagem-grande.jpg" class="w-100 border-rd-8">
            </div>
        </div>
    </div>
</div>
```

## Controle via JavaScript

```javascript
// Obter instância do modal
const modal = document.querySelector('[data-modal="meu-modal"]');

// Abrir programaticamente
modal.dispatchEvent(new Event('show'));

// Fechar programaticamente  
modal.dispatchEvent(new Event('hide'));
```
