FROM dunglas/frankenphp:php8.2

ENV SERVER_NAME=":80"

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    gnupg \
    unixodbc-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN install-php-extensions \
    gd \
    pcntl \
    opcache \
    pdo \
    sqlsrv \
    pdo_sqlsrv \
    pdo_mysql \
    zip \
    bcmath \
    intl \
    exif

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Composer deps (cached layer)
COPY composer.json composer.lock ./
RUN COMPOSER_ALLOW_SUPERUSER=1 \
    composer install --no-dev --prefer-dist --optimize-autoloader

# App source
COPY . .

# Laravel optimization
RUN php artisan package:discover --ansi || true
RUN php artisan storage:link || true
RUN chown -R www-data:www-data storage bootstrap/cache

COPY ./.docker/php/php.ini /usr/local/etc/php/

ENTRYPOINT ["php", "artisan", "octane:frankenphp"]
