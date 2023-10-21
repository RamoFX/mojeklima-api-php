FROM php:8.2-apache
RUN a2enmod rewrite
COPY custom.conf /etc/apache2/conf-available/custom.conf
RUN ln -s /etc/apache2/conf-available/custom.conf /etc/apache2/conf-enabled/custom.conf
COPY src /var/www/html/src
COPY vendor /var/www/html/vendor
COPY composer.json composer.lock /var/www/html/
EXPOSE 80
CMD ["apache2-foreground"]
