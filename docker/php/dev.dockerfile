FROM php:8.3-fpm

RUN apt update && \
    apt upgrade -y && \
    apt install -y git unzip 7zip

# https://pecl.php.net/package/xdebug
# https://pecl.php.net/package/redis
RUN pecl channel-update pecl.php.net && \
    pecl install xdebug-3.4.1 redis && \
    docker-php-ext-enable redis && \
    mkdir -p /var/run/php && \
    mkdir -p /var/log/php && \
    chmod -R 777 /var/log/php

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

EXPOSE 9000
WORKDIR /var/www
CMD composer install
ENTRYPOINT php-fpm
