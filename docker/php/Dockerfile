FROM php:8.1.0-fpm-alpine

# Install packages
RUN apk add --no-cache curl git build-base zlib-dev oniguruma-dev autoconf bash linux-headers libzip zip libzip-dev
RUN docker-php-ext-install zip

# Xdebug
ARG INSTALL_XDEBUG=false
RUN if [ ${INSTALL_XDEBUG} = true ]; \
    then \
      pecl install xdebug && docker-php-ext-enable xdebug; \
    fi;

ADD ./docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
ADD ./docker/php/extra.ini /usr/local/etc/php/conf.d/extra.ini

# Configure non-root user.
ARG PUID=1000
ARG PGID=1000
RUN apk --no-cache add shadow && \
    groupmod -o -g ${PGID} www-data && \
    usermod -o -u ${PUID} -g www-data www-data

# Source code
RUN chown www-data:www-data /var/www
COPY --chown=www-data:www-data ./ /var/www
WORKDIR /var/www
USER www-data

# Composer
COPY --from=composer:2.2.0 /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction

CMD ["php-fpm"]

EXPOSE 9000
