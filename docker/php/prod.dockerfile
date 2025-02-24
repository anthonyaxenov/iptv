FROM php:8.4-fpm

RUN apt update && \
    apt upgrade -y && \
    apt install -y --no-install-recommends git && \
    apt-get clean autoclean && \
    apt-get autoremove --yes && \
    rm -rf /var/lib/{apt,dpkg,cache,log}/

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

EXPOSE 9000
WORKDIR /var/www
CMD composer install
ENTRYPOINT php-fpm
