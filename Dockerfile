FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Copy and set up entrypoint script for Cloud Run PORT environment variable
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Use PORT environment variable (Cloud Run requirement, defaults to 8080)
ENV PORT=8080
EXPOSE 8080

# Use custom entrypoint that configures Apache to listen on PORT
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
