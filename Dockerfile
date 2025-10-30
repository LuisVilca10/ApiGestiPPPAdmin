# Dockerfile para Laravel 12 (PHP 8.2)
FROM php:8.2-fpm

# argumentos/variables (puedes cambiarlos si quieres)
ARG USER=www-data
ARG UID=1000

# instalar dependencias del sistema y extensiones PHP comunes
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libonig-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    zip \
    curl \
    ca-certificates \
    gnupg \
    default-mysql-client \
    supervisor \
    && docker-php-ext-install pdo pdo_mysql zip bcmath intl mbstring gd \
    && pecl install xdebug-3.2.0 || true \
    && docker-php-ext-enable xdebug || true \
    && rm -rf /var/lib/apt/lists/*

# instalar composer (v2+)
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# crear directorio de la app
WORKDIR /var/www/html

# copia files de composer primero para usar cache de layer
COPY composer.json composer.lock /var/www/html/

# instalar dependencias de composer (sin scripts aún)
RUN composer install --no-dev --no-scripts --prefer-dist --no-autoloader --no-interaction --no-progress || true

# copiar el resto del proyecto
COPY . /var/www/html

# permisos
RUN chown -R ${USER}:${USER} /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache || true

# instalar dependencias de composer definitivas (ejecuta autoload y scripts si necesario)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# copiar entrypoint y dar permisos
COPY docker/php/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Exponer socket/puerto fpm
EXPOSE 9000

# command
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
