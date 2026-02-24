#!/bin/sh
set -e

echo "Ensuring database exists..."

# Create database if it does not exist
php bin/console doctrine:database:create --if-not-exists --no-interaction || true

echo "Running migrations..."

# Run migrations if needed
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "Starting supervisord..."

exec /usr/bin/supervisord -n
