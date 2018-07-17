FROM php:7.2.7-apache

COPY ./docker/web/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/app

# Install build dependencies, e.g. composer, system libs
RUN apt-get update 1> /dev/null \
    && apt-get install -y git zip unzip 1> /dev/null \
    && php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
    && apt-get -y autoremove 1> /dev/null \
    && apt-get clean 1> /dev/null \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# PHP
RUN pecl install mongodb-1.5.1 1> /dev/null \
    && docker-php-ext-enable mongodb \
    && chown -R www-data:www-data /var/www/app
