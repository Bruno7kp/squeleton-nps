# Squeleton NPS

Plataforma de pesquisas NPS com backend em PHP Slim + SQLite e frontend orientado por HTMX, VanJS e Squeleton.dev.

## Sobre o projeto

Este repositorio implementa um sistema de pesquisa no estilo Survey, com foco em NPS e embed em sites externos.

Escopo macro:
- Home showcase para demonstrar bibliotecas integradas.
- Painel admin para gestao de projetos, pesquisas e respostas.
- Widget JS embutivel para captura de respostas por gatilho.

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

## Status atual

- Fase 0 (fundacao tecnica) implementada.
- Proximas entregas: modelagem completa do dominio NPS (Fase 1).
