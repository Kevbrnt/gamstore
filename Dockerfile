FROM php:8.1-apache

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql
# Installer les dépendances et extensions nécessaires
RUN apt-get update && apt-get install -y libpq-dev \
RUN docker-php-ext-install pdo pdo_pgsql

# Activer le module de réécriture d'Apache
RUN a2enmod rewrite

# Copier les fichiers du projet
COPY . /var/www/html/

# Configurer les permissions
RUN chown -R www-data:www-data /var/www/html

# Configurer Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80

CMD ["apache2-foreground"]
