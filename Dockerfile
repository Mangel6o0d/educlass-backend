FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

RUN echo 'Listen ${PORT}' > /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/' /etc/apache2/sites-enabled/000-default.conf

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

COPY init.sql /docker-entrypoint-initdb.d/init.sql

EXPOSE 80

CMD ["apache2-foreground"]