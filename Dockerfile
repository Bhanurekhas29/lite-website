# ---- Build the React website ----
FROM node:22-alpine AS web
WORKDIR /app/frontend
COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci
COPY frontend/ ./
RUN npm run build

# ---- PHP + Apache: serves the website, the API and the images ----
FROM php:8.3-apache
RUN apt-get update \
 && apt-get install -y --no-install-recommends libpng-dev libjpeg-dev libwebp-dev ca-certificates \
 && rm -rf /var/lib/apt/lists/* \
 && docker-php-ext-configure gd --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" gd pdo_mysql \
 && a2enmod headers rewrite
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/ports.conf /etc/apache2/ports.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY --from=web /app/frontend/dist/ /var/www/html/
COPY backend/ /var/www/backend/
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh && chown -R www-data:www-data /var/www/backend/media
EXPOSE 10000
CMD ["entrypoint.sh"]
