FROM php:8.0-cli

RUN apt-get update -y \
    && apt-get install -y \
    zip

RUN pecl channel-update pecl.php.net \
    && pecl install xdebug-3.1.6 \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/

