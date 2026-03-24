FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
    postgresql-dev \
    icu-dev \
    && docker-php-ext-install \
        pdo_pgsql \
        intl \
        opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

FROM base AS dev
COPY composer.json composer.lock* ./
RUN composer install --no-scripts --no-interaction
ENV APP_ENV=dev
EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]

FROM base AS prod
COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-interaction
COPY . .
ENV APP_ENV=prod
EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
