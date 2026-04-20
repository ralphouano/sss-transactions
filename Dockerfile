FROM php:8.2-cli-bookworm AS php-base

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libpq-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql zip bcmath gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM php-base AS php-deps

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

COPY . .

RUN php artisan package:discover --ansi

FROM node:22-bookworm AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js postcss.config.js tailwind.config.js tsconfig.json ./
COPY --from=php-deps /var/www/html/vendor/tightenco/ziggy ./vendor/tightenco/ziggy
RUN npm run build

FROM php-base AS runtime

WORKDIR /var/www/html

COPY --from=php-deps /var/www/html ./
COPY --from=frontend /app/public/build ./public/build

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/framework/testing bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 10000

CMD ["sh", "-c", "php artisan migrate --force && php artisan db:seed --class=RoleSeeder --force && php artisan permission:cache-reset && php artisan optimize && php -S 0.0.0.0:${PORT:-10000} -t public"]
