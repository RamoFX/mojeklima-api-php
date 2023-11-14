#FROM php:8.2-fpm
#
#ARG USER
#ARG USER_ID
#ARG GROUP_ID
#
#WORKDIR /var/www
#
#RUN apt-get update
#RUN apt-get install -y curl
#RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
#RUN docker-php-ext-install pdo pdo_mysql
#RUN pecl install redis
#RUN docker-php-ext-enable redis.so

#RUN groupadd --force -g $GROUP_ID $USER
#RUN useradd -ms /bin/bash --no-user-group -g $GROUP_ID -u 1337 $USER
#RUN usermod -u $USER_ID $USER



FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

RUN php -m | grep redis

EXPOSE 9000

USER $USER
