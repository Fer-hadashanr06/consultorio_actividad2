FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json ./
RUN npm install

COPY . .
RUN npm run build


FROM php:8.3-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libpq-dev libicu-dev libzip-dev libxml2-dev \
    libonig-dev \
    && docker-php-ext-install \
    pdo_pgsql mbstring bcmath exif pcntl intl zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && mkdir -p storage/framework/cache \
       storage/framework/sessions \
       storage/framework/views \
       bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY --from=assets /app/public/build ./public/build

ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 10000

CMD ["sh", "-c", "php artisan serve --host 0.0.0.0 --port ${PORT:-10000}"]
