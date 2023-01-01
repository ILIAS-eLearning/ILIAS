FROM php:8.1-fpm

RUN apt-get update && apt-get install -y

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        zlib1g-dev \
        libxml2-dev \
        libxslt-dev \
        libzip-dev \
    && docker-php-ext-install \
        zip \
        intl \
		mysqli \
        pdo pdo_mysql \
        opcache \
        xml \
        xsl\
    && docker-php-ext-enable \
        intl \
        opcache

RUN apt-get update && apt-get install -y \
    		libfreetype6-dev \
    		libjpeg62-turbo-dev \
    		libpng-dev \
    	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
    	&& docker-php-ext-install -j$(nproc) gd

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
RUN composer install
RUN composer update
CMD php-fpm


COPY / /var/www/ilias
WORKDIR /var/www/ilias

RUN mkdir -p /var/www/logs && chown www-data:www-data /var/www/logs
RUN mkdir -p /var/www/files && chown www-data:www-data /var/www/files
RUN mkdir -p /var/www/html && chown www-data:www-data /var/www/html

EXPOSE 9000
