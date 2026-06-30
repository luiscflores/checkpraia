FROM php:8.2-apache

# Dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    libonig-dev

# Extensões PHP necessárias para Laravel
RUN docker-php-ext-install pdo pdo_mysql mbstring zip gd

# Ativar mod_rewrite (Laravel precisa disto)
RUN a2enmod rewrite

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar projeto
COPY . .

# Instalar dependências
RUN composer install --no-dev --optimize-autoloader

# Permissões do Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

# Apache apontado para Laravel public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf