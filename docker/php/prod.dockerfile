FROM php:8.2-fpm

RUN apt update && \
    apt upgrade -y && \
    apt install -y git

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

EXPOSE 9000
WORKDIR /var/www
CMD composer install
ENTRYPOINT php-fpm
