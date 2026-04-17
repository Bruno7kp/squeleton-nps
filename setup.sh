#!/usr/bin/env sh
set -eu

printf "\n[1/6] Preparando .env...\n"
if [ ! -f .env ]; then
  cp .env.example .env
  echo "Arquivo .env criado a partir do .env.example"
else
  echo "Arquivo .env ja existe"
fi

printf "\n[2/6] Instalando dependencias PHP (Composer)...\n"
composer install

printf "\n[3/6] Instalando dependencias frontend (NPM), se houver package.json...\n"
if [ -f package.json ]; then
  npm install
else
  echo "Sem package.json, etapa ignorada"
fi

printf "\n[4/6] Executando migracoes...\n"
php scripts/migrate.php

printf "\n[5/6] Executando seed...\n"
php scripts/seed.php

printf "\n[6/6] Subindo Docker...\n"
docker compose up -d --build

echo "\nSetup concluido. Acesse: http://localhost:8080"
