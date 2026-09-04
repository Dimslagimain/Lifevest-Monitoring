# ============================
# Stage 1: Build frontend assets
# ============================
FROM node:20-alpine AS assets

WORKDIR /app

COPY package*.json ./

RUN npm ci

COPY . .

RUN npm run build


# ============================
# Stage 2: PHP application
# ============================
FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        gd \
        pdo \
        pdo_mysql \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*


# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app


# Copy composer files terlebih dahulu agar cache Docker optimal
COPY composer.json composer.lock ./


# Install Laravel dependencies
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction


# Copy seluruh aplikasi
COPY . .

COPY --from=assets /app/public/build ./public/build

RUN chmod -R 775 storage bootstrap/cache

RUN php artisan storage:link || true

RUN touch storage/logs/laravel.log

EXPOSE 8080

CMD ["sh", "-c", "tail -f storage/logs/laravel.log & php -S 0.0.0.0:${PORT:-8080} -t public"]