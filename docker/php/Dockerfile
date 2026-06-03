FROM php:8.2-fpm-alpine

# Dépendances système
RUN apk add --no-cache \
    git curl unzip libzip-dev icu-dev oniguruma-dev bash

# Extensions PHP nécessaires pour Symfony + MySQL
RUN docker-php-ext-install \
    pdo pdo_mysql zip intl opcache mbstring

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]