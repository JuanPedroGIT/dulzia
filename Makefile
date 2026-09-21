.PHONY: up down rebuild logs shell migrate migration-diff cache-clear \
        test test-unit test-integration test-setup test-frontend \
        install composer-require npm-install sync-vendor sync-npm \
        prod-up prod-down prod-logs

up:
	docker compose up -d

down:
	docker compose down

rebuild:
	docker compose down -v
	docker compose build --no-cache
	docker compose up -d

logs:
	docker compose logs -f dulzia-backend

shell:
	docker compose exec dulzia-backend bash

migrate:
	docker compose exec dulzia-backend php bin/console doctrine:migrations:migrate --no-interaction

migration-diff:
	docker compose exec dulzia-backend php bin/console doctrine:migrations:diff

cache-clear:
	docker compose exec dulzia-backend php bin/console cache:clear

# ─── Tests ──────────────────────────────────────────────────────────────────

test:
	$(MAKE) test-unit
	$(MAKE) test-integration
	$(MAKE) test-frontend

test-unit:
	docker compose exec -T dulzia-backend php vendor/bin/phpunit tests/Unit

test-integration: test-setup
	docker compose exec -T dulzia-backend php vendor/bin/phpunit tests/Integration

# Crea la BD de test (dulzia_test) en el postgres compartido — idempotente
test-setup:
	docker exec -i shared-postgres-db psql -U postgres -tAc "SELECT 1 FROM pg_database WHERE datname='dulzia_test'" | grep -q 1 \
		|| docker exec -i shared-postgres-db psql -U postgres -c "CREATE DATABASE dulzia_test OWNER dulzia"

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
	docker cp dulzia-backend:/var/www/html/vendor ./backend/vendor

# Copia node_modules del contenedor → disco local (IDE lo necesita)
# Nota: usa --archive para preservar symlinks en Linux→Windows
sync-npm:
	docker cp dulzia-frontend:/app/node_modules ./frontend/

build-front:
	docker compose exec frontend npm run build

# ─── Producción (servidor compartido, docker-compose.prod.yml) ──────────────

prod-up:
	docker compose -f docker-compose.prod.yml up -d --build

prod-down:
	docker compose -f docker-compose.prod.yml down

prod-logs:
	docker compose -f docker-compose.prod.yml logs -f
