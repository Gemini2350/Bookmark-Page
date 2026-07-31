# All-in-one image: Apache/PHP + MariaDB in a single container.
FROM php:8.4-apache
RUN a2enmod expires
RUN docker-php-ext-install mysqli
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN apt-get update \
    && apt-get install -y --no-install-recommends mariadb-server curl \
    && rm -rf /var/lib/apt/lists/*
COPY --chown=www-data:www-data ./www /var/www/html
# Adapt the MySQL dump for MariaDB: portable collation, DB is created by the entrypoint
RUN mkdir /docker-init
COPY ./dump/myDb.sql /docker-init/myDb.sql
RUN sed -i -e 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' \
        -e '/^CREATE DATABASE/d' -e '/^USE /d' /docker-init/myDb.sql \
    && sed -i 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' /var/www/html/php/myDb.sql
COPY ./docker/standalone-entrypoint.sh /usr/local/bin/standalone-entrypoint.sh
RUN chmod +x /usr/local/bin/standalone-entrypoint.sh
ENV DB_HOST=127.0.0.1 \
    DB_USER=bookmark \
    DB_PASSWORD=bookpass \
    DB_NAME=bookmark-db
VOLUME /var/lib/mysql
# php:apache sets STOPSIGNAL SIGWINCH (Apache graceful stop); our entrypoint
# manages both services and shuts down on SIGTERM
STOPSIGNAL SIGTERM
HEALTHCHECK --interval=30s --timeout=5s --start-period=60s \
    CMD curl -fs http://localhost/php/getGlobal.php || exit 1
ENTRYPOINT ["standalone-entrypoint.sh"]
