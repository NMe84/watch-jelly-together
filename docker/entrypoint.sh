#!/bin/sh
set -e

echo "Ensuring database exists..."

# Create database if it does not exist
php bin/console doctrine:schema:create --no-interaction || true

echo "Running migrations..."

# Run migrations if needed
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true

echo "Starting supervisord..."

exec /usr/bin/supervisord -n
