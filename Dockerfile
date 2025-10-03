# Apache + PHP 8.2 (serves HTTP on port 80)
FROM php:8.2-apache

# Enable needed PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Configure Apache to serve /var/www/html
WORKDIR /var/www/html
COPY html/ /var/www/html/

# Make sure writable dir exists (adjust as needed for your app)
RUN mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html

# Apache in this image already listens on 80
EXPOSE 80
