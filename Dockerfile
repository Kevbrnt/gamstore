FROM php:8.2-apache

# Installer les dépendances système nécessaires
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev

# Installer Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get update && apt-get install -y nodejs

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql zip
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Activer le module Apache mod_rewrite
RUN a2enmod rewrite

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copier les fichiers de l'application
COPY . /var/www/html/

# Définir les permissions
RUN chown -R www-data:www-data /var/www/html

# Installer les dépendances Composer
WORKDIR /var/www/html
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader

# Installer les dépendances Node.js
RUN npm install

# Exposer le port 80
EXPOSE 80

# Démarrer Apache
CMD ["apache2-foreground"]