#!/bin/bash
set -e

HOST="${POSTGRES_HOST:-shared-postgres-db}"
SUPERUSER="${POSTGRES_USER:-postgres}"
PASS="${DULZIA_DB_PASS:-dulzia_pass}"

until PGPASSWORD="$POSTGRES_PASSWORD" pg_isready -h "$HOST" -U "$SUPERUSER" -q; do
  echo "Waiting for postgres..."
  sleep 2
done

PGPASSWORD="$POSTGRES_PASSWORD" psql -h "$HOST" -U "$SUPERUSER" -d postgres <<-EOSQL
	DO \$\$ BEGIN
	  CREATE USER dulzia WITH ENCRYPTED PASSWORD '$PASS';
	EXCEPTION WHEN duplicate_object THEN NULL;
	END \$\$;

	SELECT 'CREATE DATABASE dulzia OWNER dulzia'
	WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'dulzia')\gexec

	GRANT ALL PRIVILEGES ON DATABASE dulzia TO dulzia;
EOSQL

PGPASSWORD="$POSTGRES_PASSWORD" psql -h "$HOST" -U "$SUPERUSER" -d dulzia \
  -c "GRANT ALL ON SCHEMA public TO dulzia;"

echo "dulzia database ready."
