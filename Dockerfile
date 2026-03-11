# ──────────────────────────────────────────────
# Stage 1 – base: PHP 8.3 + system deps
# ──────────────────────────────────────────────
FROM php:8.3-fpm-alpine AS base

# System dependencies
RUN apk --no-cache add \
    bash \
    curl \
    git \
    unzip \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    linux-headers

# PHP extensions
RUN docker-php-ext-install \
    bcmath \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    zip \
    pcntl \
    intl

# Redis extension via PECL
RUN pecl install redis && docker-php-ext-enable redis

# Composer 2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /api

# ──────────────────────────────────────────────
# Stage 2 – dependencies: install vendor
# ──────────────────────────────────────────────
FROM base AS dependencies

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

# ──────────────────────────────────────────────
# Stage 3 – production
# ──────────────────────────────────────────────
FROM base AS production

COPY --from=dependencies /api/vendor ./vendor
COPY . .

COPY ./.docker/php-fpm/php.ini      /usr/local/etc/php/php.ini
COPY ./.docker/php-fpm/www.conf     /usr/local/etc/php-fpm.d/www.conf
COPY ./.docker/php-fpm/php-fpm.conf /usr/local/etc/php-fpm.conf

RUN chown -R www-data:www-data /api/storage /api/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
