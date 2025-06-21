#FROM php:8.3-fpm
FROM php:8.3-apache
RUN a2enmod rewrite

COPY php-dev.ini /usr/local/etc/php/conf.d/99-dev.ini

WORKDIR /var/www/laravel

RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    unzip \
    zip \
    git \
    libzip-dev \
    libpq-dev \
    curl \
    mc \
    && apt-get clean

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mysqli gd mbstring bcmath zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

RUN mkdir -p /home/www-data && \
    chown -R www-data:www-data /home/www-data && \
    usermod -d /home/www-data www-data

RUN chown -R www-data:www-data /var/www/laravel

USER www-data
