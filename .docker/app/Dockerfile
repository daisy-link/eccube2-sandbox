FROM php:5.6-apache AS base

RUN set -x \
    && apt-get update \
    && apt-get install -y \
        iproute2 \
        sudo \
    && apt-get clean \
    && rm -rf /tmp/*

RUN \
    apt-get update \
    && apt-get install -y \
        libfreetype6-dev \
        libjpeg-dev \
        libmcrypt-dev \
        libpng-dev \
        libzip-dev \
    && docker-php-ext-configure gd \
        --with-freetype-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/ \
        --with-png-dir=/usr/include/ \
    && docker-php-ext-install \
        gd \
        mcrypt \
        mysql \
        pdo_mysql \
        zip

COPY ./base.ini /usr/local/etc/php/conf.d/base.ini
COPY ./entrypoint-base.sh /entrypoint-base.sh
ENTRYPOINT ["/entrypoint-base.sh"]
CMD ["apache2-foreground"]

FROM base AS dev

RUN \
    pecl install xdebug-2.5.5 \
    && docker-php-ext-enable \
        xdebug \
    && rm -rf /tmp/*

COPY ./dev.ini /usr/local/etc/php/conf.d/dev.ini
COPY ./entrypoint-dev.sh /entrypoint-dev.sh
ENTRYPOINT ["/entrypoint-dev.sh"]
CMD ["apache2-foreground"]
