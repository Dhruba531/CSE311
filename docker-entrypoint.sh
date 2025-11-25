#!/bin/bash
set -e

# Get PORT from environment variable (Cloud Run provides this)
PORT=${PORT:-8080}

# Configure Apache to listen on the PORT environment variable
# Replace any existing Listen directive with the PORT
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf

# Update VirtualHost to use PORT
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# If Listen directive doesn't exist, add it
if ! grep -q "^Listen" /etc/apache2/ports.conf; then
    echo "Listen ${PORT}" >> /etc/apache2/ports.conf
fi

# Execute the original Apache entrypoint
exec docker-php-entrypoint apache2-foreground

