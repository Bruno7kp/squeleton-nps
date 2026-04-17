# JS-Cookie Reference

Biblioteca para gerenciamento de cookies do navegador de forma simples e limpa. Útil para salvar estados persistentes como preferências do usuário e configurações de interface.

## Métodos Principais

| Método | Descrição |
|--------|-----------|
| `Cookies.set(nome, valor, opções)` | Cria um cookie |
| `Cookies.get(nome)` | Obtém o valor de um cookie |
| `Cookies.get()` | Retorna todos os cookies como objeto |
| `Cookies.remove(nome, opções)` | Remove um cookie |

## Opções

| Opção | Tipo | Descrição |
|-------|------|-----------|
| `expires` | number | Dias até expirar (ex: 365) |
| `path` | string | Caminho onde o cookie está disponível (ex: '/') |
| `domain` | string | Domínio onde o cookie está disponível |
| `secure` | boolean | Cookie só enviado em conexões HTTPS |
| `sameSite` | string | Restringe envio cross-site ('strict', 'lax', 'none') |

## Exemplo: Aviso LGPD

### Marcação HTML
```html
<div id="lgpd" class="fadeInUp d-none">
    <div class="row f-items-center lgpd-consent m-15-b md-m-15-lr">
        <div class="c-lg-10 c-md-9 c-xs-12 lgpd-text">
            Utilizamos cookies essenciais e tecnologias semelhantes para analisar e
            aprimorar nosso serviço, personalizar conteúdos e melhorar sua experiência
            digital de acordo com a nossa <a href="/termos/" target="_blank">Política de Privacidade</a>
            e, ao continuar navegando, você concorda com estas condições.
        </div>
        <div class="c-lg-2 c-md-3 c-xs-12 sm-p-20-t">
            <a href="#" onclick="return false;" class="lgpd-btn sm-w-100">
                Prosseguir
            </a>
        </div>
    </div>
</div>
<div id="lgpd-bg" class="d-none"></div>
```

### JavaScript
```html
<script>
document.addEventListener('DOMContentLoaded', function () {
    var lgpdElement = document.getElementById('lgpd');
    var lgpdBg = document.getElementById('lgpd-bg');

    if (lgpdElement) {
        // Verifica se o cookie de consentimento já existe
        if (Cookies.get('lgpd') !== "yes") {
            // Exibe o banner se o consentimento não foi dado
            lgpdElement.classList.remove('d-none');
            lgpdElement.classList.add('animated');
            lgpdBg.classList.remove('d-none');
        }

        // Adiciona evento ao botão de consentimento
        var lgpdButton = document.querySelector('.lgpd-btn');
        if (lgpdButton) {
            lgpdButton.addEventListener('click', function () {
                // Salva o consentimento por 1 ano
                Cookies.set('lgpd', 'yes', { expires: 365, path: '/' });

                // Esconde o banner
                lgpdElement.classList.remove('animated');
                lgpdElement.classList.add('d-none');
                lgpdBg.classList.add('d-none');
            });
        }
    }
});
</script>
```

## Fluxo do Aviso LGPD

1. **Verificação inicial**: Ao carregar a página, verifica se o cookie 'lgpd' existe
2. **Exibição condicional**: Mostra o banner apenas se o consentimento não foi dado
3. **Consentimento**: Ao clicar em "Prosseguir", salva o cookie e esconde o banner
4. **Persistência**: Cookie válido por 365 dias em todo o site (path: '/')

## Exemplos de Uso

### Criar cookie simples
```javascript
Cookies.set('usuario', 'joao');
```

### Criar cookie com expiração
```javascript
// Expira em 7 dias
Cookies.set('sessao', 'abc123', { expires: 7 });
```

### Criar cookie seguro
```javascript
Cookies.set('token', 'xyz789', {
    expires: 30,
    path: '/',
    secure: true,
    sameSite: 'strict'
});
```

### Ler cookie
```javascript
var usuario = Cookies.get('usuario'); // 'joao'
```

### Ler todos os cookies
```javascript
var todos = Cookies.get(); // { usuario: 'joao', sessao: 'abc123' }
```

### Remover cookie
```javascript
Cookies.remove('usuario');

// Se o cookie foi criado com path específico
Cookies.remove('sessao', { path: '/' });
```

## Casos de Uso Comuns

- **Consentimento LGPD/GDPR**: Salvar aceite de cookies
- **Preferências de tema**: Dark mode / Light mode
- **Idioma**: Preferência de idioma do usuário
- **Carrinho de compras**: Itens temporários antes do login
- **Última visita**: Data/hora da última visita
- **Fechamento de banners**: Lembrar que usuário fechou aviso
