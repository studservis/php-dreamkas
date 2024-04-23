FROM php:7.4-cli

RUN apt-get update -y \
    && apt-get install -y \
    zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP extensions
#RUN docker-php-ext-install json

COPY ./ /var/www/

WORKDIR /var/www/

