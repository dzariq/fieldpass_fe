FROM --platform=linux/arm64/v8 php:8.2-fpm

WORKDIR /var/www/dashboard

# Add docker-php-extension-installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip unzip \
    git curl vim \
    locales \
    jpegoptim optipng pngquant gifsicle \
    libmemcached-dev \
    supervisor \
    nginx && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP Extensions
RUN install-php-extensions mbstring pdo_mysql zip exif pcntl gd memcached sockets

COPY --chown=www-data:www-data . /var/www/dashboard
RUN chmod -R 775 /var/www/dashboard/storage

# PHP Error Log
RUN mkdir -p /var/log/php && \
    touch /var/log/php/errors.log && chmod 777 /var/log/php/errors.log

COPY supervisord.conf /etc/supervisord.conf
COPY php/local.ini /usr/local/etc/php/conf.d/app.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --optimize-autoloader --no-dev

RUN chmod +x /var/www/dashboard/run.sh

EXPOSE 80
CMD ["/bin/sh", "/var/www/dashboard/run.sh"]
