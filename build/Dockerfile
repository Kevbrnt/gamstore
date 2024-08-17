FROM php:8.3-apache

# Installer les dépendances système et les extensions PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    postgresql-client \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip pgsql \
    && pecl install mongodb && docker-php-ext-enable mongodb \
    && a2enmod rewrite

# Installer Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get update \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier composer.json et composer.lock du dossier build
COPY build/composer.json build/composer.lock* ./

# Afficher le contenu du répertoire pour le débogage
RUN ls -la

# Installer les dépendances Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs -vvv

# Copier le reste des fichiers de l'application
COPY . .

# Définir les permissions
RUN chown -R www-data:www-data .

# Installer les dépendances Node.js de production
COPY build/package.json build/package-lock.json* ./
RUN npm ci --only=production

# Exposer le port 80
EXPOSE 80

# Démarrer Apache
CMD ["apache2-foreground"]