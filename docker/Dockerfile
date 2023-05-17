FROM php:8.1-fpm-alpine

WORKDIR /app

RUN apk update && \
    apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    zlib-dev \
    libzip-dev \
    zip \
    unzip \
    nginx

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql gd zip

COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
#RUN composer install --no-scripts --no-autoloader

# Copy the rest of the application code to the container
COPY . .

EXPOSE 9000

CMD php-fpm