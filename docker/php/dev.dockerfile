FROM php:8.2-fpm

RUN apt update && \
    apt upgrade -y && \
    apt install -y git unzip 7zip

# https://pecl.php.net/package/xdebug
RUN pecl channel-update pecl.php.net && \
    pecl install xdebug-3.3.2 unzip && \
    mkdir -p /var/log/php

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

EXPOSE 9000
WORKDIR /var/www
CMD composer install
ENTRYPOINT php-fpm
