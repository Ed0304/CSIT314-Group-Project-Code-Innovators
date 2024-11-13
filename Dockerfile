# Use a base image with PHP and MySQL support
FROM php:7.4-apache

# Install PHP extensions and MySQL client
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy project files to the web server directory
COPY . /var/www/html

# Set permissions for web server
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80 for the web server
EXPOSE 80

# Set the entry point to start Apache
CMD ["apache2-foreground"]
