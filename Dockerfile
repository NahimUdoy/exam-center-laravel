FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

FROM php:8.4-cli-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libonig-dev libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite zip gd bcmath \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
COPY --from=assets /app/public/build ./public/build
ENV APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=sqlite \
    LOG_CHANNEL=stderr \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=sync \
    COMPOSER_ALLOW_SUPERUSER=1
RUN echo 'memory_limit=1024M' > /usr/local/etc/php/conf.d/memory.ini \
    && composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts \
    && mkdir -p database storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/public bootstrap/cache \
    && touch database/database.sqlite \
    && cp .env.example .env \
    && php artisan key:generate --force \
    && php artisan package:discover --ansi \
    && php artisan migrate --force \
    && php artisan db:seed --force \
    && chmod -R ug+rwx storage bootstrap/cache database
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh
EXPOSE 10000
CMD ["/usr/local/bin/start.sh"]
