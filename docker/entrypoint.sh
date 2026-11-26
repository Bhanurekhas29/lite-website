#!/bin/sh
# Hosting services tell the app which port to use through PORT.
export PORT="${PORT:-10000}"
mkdir -p /var/www/backend/media/uploads
chown -R www-data:www-data /var/www/backend/media 2>/dev/null || true
# One-time image updates (does nothing once applied; never stops the site from starting).
php /var/www/backend/database/migrate_image_updates.php || true
exec apache2-foreground
