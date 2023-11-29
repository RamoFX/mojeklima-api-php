FROM php:8.2-fpm-alpine

RUN apk add --no-cache $PHPIZE_DEPS
RUN pecl install redis
RUN apk del $PHPIZE_DEPS
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-enable redis

# php-fpm config
# RUN echo 'pm = dynamic' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
#     echo 'pm.max_children = 12' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
#     echo 'pm.start_servers = 6' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
#     echo 'pm.min_spare_servers = 4' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
#     echo 'pm.max_spare_servers = 8' >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
#     echo 'pm.max_requests = 0' >> /usr/local/etc/php-fpm.d/zz-docker.conf

# php config
ARG APP_MODE
RUN cp "/usr/local/etc/php/php.ini-$APP_MODE" /usr/local/etc/php/php.ini

EXPOSE 9000
