FROM php:8.3.7-fpm-bookworm

WORKDIR /app-php

RUN apt update -y && apt install -y git libpq-dev libzip-dev
RUN docker-php-ext-install pdo_pgsql pgsql zip

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY install_composer.sh ./
RUN ./install_composer.sh
RUN rm ./install_composer.sh

COPY bin bin

COPY public public
COPY templates templates
COPY translations translations
COPY .env ./
COPY composer.* ./
COPY importmap.php ./
COPY symfony.lock ./
COPY migrations migrations
COPY config config
COPY src src

RUN php composer.phar i -n

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY entrypoint.sh ./

ENTRYPOINT ["/bin/bash","/app-php/entrypoint.sh"]