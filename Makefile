.PHONY: up down rebuild logs shell migrate migration-diff cache-clear \
        test test-unit test-integration test-frontend \
        install composer-require npm-install sync-vendor sync-npm

up:
	docker compose up -d

down:
	docker compose down

rebuild:
	docker compose down -v
	docker compose build --no-cache
	docker compose up -d

logs:
	docker compose logs -f backend

shell:
	docker compose exec backend bash

migrate:
	docker compose exec backend php bin/console doctrine:migrations:migrate --no-interaction

migration-diff:
	docker compose exec backend php bin/console doctrine:migrations:diff

cache-clear:
	docker compose exec backend php bin/console cache:clear

# ─── Tests ──────────────────────────────────────────────────────────────────

test:
	$(MAKE) test-unit
	$(MAKE) test-integration
	$(MAKE) test-frontend

test-unit:
	docker compose exec backend php bin/phpunit tests/Unit

test-integration:
	docker compose exec backend php bin/phpunit tests/Integration

test-frontend:
	npm --prefix frontend run test

# ─── Dependencias ───────────────────────────────────────────────────────────

# Primera instalación completa (tras clonar el repo)
install:
	docker compose exec backend composer install
	$(MAKE) sync-vendor
	npm --prefix frontend install
	$(MAKE) sync-npm

# Añadir paquete PHP
# Uso: make composer-require pkg="vendor/nombre"
composer-require:
	docker compose exec backend composer require $(pkg)
	$(MAKE) sync-vendor

# Añadir paquete npm
# Uso: make npm-install pkg="nombre"
npm-install:
	docker compose exec frontend npm install $(pkg)
	npm --prefix frontend install $(pkg)

# Copia vendor del contenedor → disco local (IDE lo necesita)
sync-vendor:
	docker cp dulziasalamanca-backend-1:/var/www/html/vendor ./backend/vendor

# Copia node_modules del contenedor → disco local (IDE lo necesita)
# Nota: usa --archive para preservar symlinks en Linux→Windows
sync-npm:
	docker cp dulziasalamanca-frontend-1:/app/node_modules ./frontend/

build-front:
	docker compose exec frontend npm run build
