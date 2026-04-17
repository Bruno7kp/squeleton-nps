SHELL := /bin/sh

.PHONY: setup up down logs migrate seed

setup:
	sh setup.sh

up:
	docker compose up -d --build

down:
	docker compose down

logs:
	docker compose logs -f

migrate:
	php scripts/migrate.php

seed:
	php scripts/seed.php
