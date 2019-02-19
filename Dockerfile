FROM jrottenberg/ffmpeg:3.4-scratch
FROM php:7.2-fpm

MAINTAINER Superbalist <tech+docker@superbalist.com>

WORKDIR /docker/php

# Packages
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get -y --allow-unauthenticated install \
        git \
        zlib1g-dev \
        unzip \
        python \
        supervisor \
        gearman-job-server \
        libgearman7 \
        libgearman-dev \
    && rm -r /var/lib/apt/lists/*

RUN cd /tmp \
    && git clone https://github.com/wcgallego/pecl-gearman.git \
    && cd pecl-gearman \
    && git checkout gearman-2.0.3 \
    && phpize \
    && ./configure \
    && make -j$(nproc) \
    && make install \
    && rm -r /tmp/pecl-gearman \
    && docker-php-ext-enable gearman

# Copy ffmpeg bins
COPY --from=mwader/static-ffmpeg:4.1 /ffmpeg /ffprobe /usr/local/bin/

# Copy supervisord config
COPY docker/supervisor/conf.d/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# PHP Extensions
RUN docker-php-ext-install -j$(nproc) zip \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install sockets 

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

CMD ["/usr/bin/supervisord"]
