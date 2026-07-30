FROM php:8.4-apache
RUN a2enmod expires
RUN docker-php-ext-install mysqli
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --chown=www-data:www-data ./www /var/www/html
