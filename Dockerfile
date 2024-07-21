FROM php:8.1-apache

# Force update and install system dependencies
RUN apt-get update && apt-get install -y libpq-dev postgresql-client

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install pdo_pgsql

# Enable Apache modules
RUN a2enmod rewrite

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Add ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf