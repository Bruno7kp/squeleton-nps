# Squeleton NPS

Plataforma de pesquisas NPS com backend em PHP Slim + SQLite e frontend orientado por HTMX, VanJS e Squeleton.dev.

## Sobre o projeto

Este repositorio implementa um sistema de pesquisa no estilo Survey, com foco em NPS e embed em sites externos.

Escopo macro:
- Home showcase para demonstrar bibliotecas integradas.
- Painel admin para gestao de projetos, pesquisas e respostas.
- Widget JS embutivel para captura de respostas por gatilhos dinamicos.

## Stack

- PHP 8.x
- Slim Framework 4
- SQLite
- Docker (PHP-FPM + Nginx)
- HTMX
- VanJS
- Squeleton.dev

## Estrutura inicial

- app/: bootstrap, rotas e infraestrutura.
- public/: entrypoint da aplicacao.
- templates/: templates server-side.
- database/: banco SQLite e migracoes.
- scripts/: utilitarios de migracao e seed.
- docker/: imagens/config de execucao local.

## Requisitos

- PHP 8.2+
- Composer 2+
- Docker + Docker Compose

Opcional:
- Node/NPM (somente quando houver frontend build)

## Configuracao de ambiente

1. Copiar variaveis de ambiente:

```bash
cp .env.example .env
```

2. Ajustar credenciais e parametros no arquivo .env.

Se a porta 8081 ja estiver em uso, altere:

```env
APP_PORT=8081
APP_URL=http://localhost:8081
```

## Rodando o projeto

### Opcao 1 - Setup unico

Linux/macOS/WSL:

```bash
sh setup.sh
```

Esse comando executa:
- copia do .env (se nao existir)
- composer install
- npm install (se houver package.json)
- migracoes
- seed
- docker compose up -d --build

### Opcao 2 - Passo a passo (PowerShell)

```powershell
Copy-Item .env.example .env -ErrorAction SilentlyContinue
composer install --no-interaction
php scripts/migrate.php
php scripts/seed.php
docker compose up -d --build
```

## Endpoints de health

Com a aplicacao no ar:
- Web health: http://localhost:${APP_PORT}/health
- API health: http://localhost:${APP_PORT}/api/health

Saida esperada (exemplo):

```json
{"status":"ok","service":"nps-api","database":"up"}
```

## Execucao local sem Docker (debug rapido)

```powershell
php -S 127.0.0.1:8090 -t public
```

Depois acesse:
- http://127.0.0.1:8090/health
- http://127.0.0.1:8090/api/health

## Seguranca e hardening

Itens aplicados no projeto:
- Session hardening no entrypoint: `session.use_strict_mode`, cookie `HttpOnly`, `SameSite=Lax` e `Secure` quando HTTPS.
- Middleware CSRF para requests mutaveis fora de `/api/*`.
- Token CSRF injetado em formulários de login/logout e CRUD do admin.
- Security headers globais:
	- `Content-Security-Policy`
	- `X-Content-Type-Options: nosniff`
	- `X-Frame-Options: SAMEORIGIN`
	- `Referrer-Policy: strict-origin-when-cross-origin`
	- `Permissions-Policy` restritiva para câmera/microfone/geolocalização.
- Error handler global com fallback:
	- JSON padronizado para rotas `/api/*`
	- página HTML amigável para rotas web.

## Widget embed

O widget é carregado a partir de `public/widget-loader.js` e foi projetado para rodar em páginas externas com CORS aberto no backend do widget.

### Snippet básico

```html
<script src="https://seu-dominio.com/widget-loader.js"
        data-nps-key="nps_pk_seu_projeto"
        data-nps-trigger="on_load"
        data-nps-auto-open="true"
  data-nps-show-float-button="true"
        defer></script>
```

### Opções de configuração

- `data-nps-key` (obrigatório): chave pública do projeto.
- `data-nps-trigger` (opcional): gatilho enviado ao backend. Default `on_load`.
- `data-nps-auto-open` (opcional): `true` ou `false`; default `true`. Se `false`, o widget carrega mas não abre automaticamente.
- `data-nps-show-float-button` (opcional): `true` ou `false`; default `true`. Se `false`, o botao flutuante nao e renderizado.
	- Se `data-nps-auto-open="false"` e `data-nps-show-float-button="false"`, o widget entra em modo lazy e so carrega pesquisa ao chamar `window.NPSWidget.openWithTrigger('seu_trigger')`.
- `data-nps-api-base` (opcional): URL base da API quando o script é servido de outro host. Default: origem do script.
- `data-nps-user-id` (opcional): valor enviado como `user_identifier`.
- `data-nps-session-id` (opcional): valor enviado como `session_identifier`.

### Como funciona

- O script localiza o elemento `<script data-nps-key>` atual.
- Garante as dependências necessárias (`Squeleton`, `VanJS`, `A11yDialog`) via CDN quando necessário.
- Faz `GET /api/widget/survey?public_key=...&trigger_event=...` para buscar a pesquisa.
	- No modo lazy (`auto-open=false` + `show-float-button=false`), esse carregamento inicial e pulado.
- Se houver pesquisa publicada mapeada para o gatilho, ela é exibida no modal.
- Se não houver mapeamento para o gatilho informado, a API responde `404`, registra o evento em log e o modal nao abre.
- O envio usa `POST /api/widget/submissions` com payload JSON:
  - `public_key`, `trigger_event`, `answers`
  - `source_url`, `user_identifier`, `session_identifier`
- O backend libera CORS para as rotas do widget (`/api/widget/*`), então o embed pode ser usado em domínios externos.

## Referencias

- Squeleton.dev: https://squeleton.dev
- Slim Framework: https://www.slimframework.com
- HTMX: https://htmx.org
- VanJS: https://vanjs.org
- Embla Carousel: https://www.embla-carousel.com
- Toastify: https://github.com/apvarun/toastify-js
- VenoBox: https://github.com/nicolafranchini/VenoBox
- Counter-Up2: https://github.com/bfintal/Counter-Up2
- Wow2: https://github.com/graingert/wow

