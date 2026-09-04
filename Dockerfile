# ============================
# Stage 1: Build Vite
# ============================
FROM node:20-alpine AS assets

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .

RUN npm run build


# ============================
# Stage 2: Laravel
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
    && docker-php-ext-install gd pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy seluruh project
COPY . .

# Install dependency PHP
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# Copy hasil Vite
COPY --from=assets /app/public/build ./public/build/

# Pastikan manifest ada
RUN test -f public/build/manifest.json

# Laravel permissions
RUN chmod -R 775 storage bootstrap/cache

RUN php artisan storage:link || true

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public"]