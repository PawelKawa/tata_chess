FROM php:8.4-cli-alpine

# System deps
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    icu-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    oniguruma-dev \
    curl-dev \
    unzip

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo_pgsql \
    zip \
    intl \
    gd \
    mbstring \
    xml \
    curl \
    pcntl \
    opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN mkdir -p bootstrap/cache storage/logs storage/framework/sessions storage/framework/views storage/framework/cache \
 && chmod -R 775 bootstrap/cache storage \
 && composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 8080

CMD php artisan migrate --force \
 && php artisan filament:assets \
 && php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache \
 && php artisan serve --host=0.0.0.0 --port=8080
