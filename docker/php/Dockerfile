FROM php:8.3-fpm-alpine

RUN apk add --no-cache bash git curl sqlite sqlite-dev $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
