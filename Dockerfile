# PHP-FPM container for the bug report application
FROM php:8.2-fpm-alpine

# Install required extensions
RUN apk add --no-cache sqlite-dev \
    && docker-php-ext-install pdo_sqlite

# Configure working directory
WORKDIR /var/www/html

# Copy application code into the image
COPY html/ /var/www/html/

# Ensure runtime directories exist and have correct ownership
RUN mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html

# Expose PHP-FPM port (for documentation purposes)
EXPOSE 9000

CMD ["php-fpm"]
