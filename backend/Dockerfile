FROM php:7.4-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install mbstring pdo_mysql \
    && pecl install pcov-1.0.11 \
    && docker-php-ext-enable pcov \
    && echo "pcov.directory=/var/www/html/app" > /usr/local/etc/php/conf.d/99-pcov.ini \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
