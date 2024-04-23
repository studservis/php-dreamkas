FROM php:7.4-cli

RUN apt-get update -y \
    && apt-get install -y \
    zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/

