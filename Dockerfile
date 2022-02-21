FROM registry.access.redhat.com/ubi8/php-74:1

WORKDIR /var/www

COPY . .

RUN composer install

