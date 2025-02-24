FROM php:8.4-fpm

RUN apt update \
    && apt upgrade -y \
    && apt install -y --no-install-recommends \
        git \
        unzip \
        7zip \
    && apt-get clean autoclean \
    && apt-get autoremove --yes \
    && rm -rf /var/lib/{apt,dpkg,cache,log}/

# https://pecl.php.net/package/xdebug
# https://pecl.php.net/package/redis
RUN pecl channel-update pecl.php.net \
    && pecl install \
        xdebug-3.4.0 \
        unzip \
        redis \
    && mkdir -p /var/log/php

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

EXPOSE 9000
WORKDIR /var/www
CMD composer install
ENTRYPOINT php-fpm
