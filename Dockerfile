FROM php:8.1-apache

# Force update and install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    postgresql-client

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Enable Apache modules
RUN a2enmod rewrite

# Configure Apache
RUN mkdir -p /var/log/apache2 \
    && chown -R www-data:www-data /var/log/apache2 \
    && echo "ErrorLog /var/log/apache2/error.log" >> /etc/apache2/apache2.conf \
    && echo "CustomLog /var/log/apache2/access.log combined" >> /etc/apache2/apache2.conf

# Add ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Configure PHP
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
