FROM php:7.4-apache
RUN a2enmod rewrite
COPY custom.conf /etc/apache2/conf-available/custom.conf
RUN ln -s /etc/apache2/conf-available/custom.conf /etc/apache2/conf-enabled/custom.conf
COPY src /var/www/html/src
COPY vendor /var/www/html/vendor
EXPOSE 80
CMD ["apache2-foreground"]
