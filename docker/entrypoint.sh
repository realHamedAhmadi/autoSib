#!/bin/sh
set -e

# Cache Laravel configuration and routes for production speed
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations if needed (optional, uncomment if desired)
# php artisan migrate --force

# Start Supervisor to run both PHP-FPM and Nginx
exec supervisord -c /etc/supervisord.conf
