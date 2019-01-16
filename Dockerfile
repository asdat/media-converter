# FROM php:7.2-fpm
FROM jrottenberg/ffmpeg:3.4-scratch
MAINTAINER Superbalist <tech+docker@superbalist.com>

WORKDIR /docker/php

#установка пакетов php внутри контейнера
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y \
         curl php7.2 php7.2-fpm php7.2-mysql php7.2-curl php7.2-mbstring

#Изменяем доступность php-fpm чтобы слушалось не только в localhost
RUN sed -i 's@listen = /run/php/php7.2-fpm.sock@listen = 9011@'  /etc/php/7.2/fpm/pool.d/www.conf

# Packages
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y \
        git \
        zlib1g-dev \
        unzip \
        python \
        supervisor \
        && ( \
            cd /tmp \
            && mkdir librdkafka \
            && cd librdkafka \
            && git clone https://github.com/edenhill/librdkafka.git . \
            && ./configure \
            && make \
            && make install \
        ) \
    && rm -r /var/lib/apt/lists/*

# MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY docker/supervisor2/conf.d/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# PHP Extensions
RUN docker-php-ext-install -j$(nproc) zip \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka



# Composer
ENV COMPOSER_HOME /composer
ENV PATH /composer/vendor/bin:$PATH
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
    && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
    && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
    && php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer --version=1.1.0 && rm -rf /tmp/composer-setup.php

# Install Composer Application Dependencies
COPY . /docker/php
RUN composer install --no-autoloader --no-scripts --no-interaction

RUN composer dump-autoload --no-interaction
