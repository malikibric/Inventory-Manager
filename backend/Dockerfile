FROM php:8.2-apache


RUN apt-get update && apt-get install -y \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


WORKDIR /var/www/html


COPY composer.json ./

RUN composer install --no-dev --optimize-autoloader


COPY . .


RUN chown -R www-data:www-data /var/www/html \
    && a2enmod rewrite


EXPOSE 80
