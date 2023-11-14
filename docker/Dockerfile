FROM php:8.2-fpm-alpine

RUN apk add --no-cache $PHPIZE_DEPS
RUN pecl install redis
RUN apk del $PHPIZE_DEPS
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-enable redis pdo pdo_mysql

EXPOSE 9000

USER $USER
