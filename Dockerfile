# Use a base image with PHP and Apache
FROM php:7.4-apache

# Install PHP extensions needed for MariaDB (mysqli and pdo_mysql)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite for pretty URLs
RUN a2enmod rewrite

# Copy project files to the web server directory
COPY . /var/www/html

# Set ownership and permissions for web server directory
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80 for the web server
EXPOSE 80

# Set the entry point to start Apache
CMD ["apache2-foreground"]
