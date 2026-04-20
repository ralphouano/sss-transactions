FROM node:22-bookworm AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js postcss.config.js tailwind.config.js tsconfig.json ./
RUN npm run build

FROM php:8.2-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libpq-dev libzip-dev \
    && docker-php-ext-install pdo_pgsql zip bcmath \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN php artisan package:discover --ansi

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/framework/testing bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 10000

CMD ["sh", "-c", "php artisan migrate --force && php artisan optimize && php -S 0.0.0.0:${PORT:-10000} -t public"]
