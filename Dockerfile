# PHP-FPM container for the bug report application
FROM php:8.2-fpm-alpine

# Install required extensions
RUN apk add --no-cache sqlite-dev \
    && docker-php-ext-install pdo_sqlite

# Configure working directory and copy app code
WORKDIR /var/www/html
COPY html/ /var/www/html/

# Ensure the writable SQLite directory exists
RUN mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html

EXPOSE 9000
CMD ["php-fpm"]