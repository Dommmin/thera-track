FROM dommin/php-8.4-fpm:latest

ARG USER_ID=1000
ARG GROUP_ID=1000

COPY docker/start.sh /usr/local/bin/start.sh
COPY docker/php/php.ini /usr/local/etc/php/conf.d/php.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf

USER root

RUN chmod +x /usr/local/bin/start.sh
RUN usermod -u ${USER_ID} www-data
RUN groupmod -g ${GROUP_ID} www-data

WORKDIR /var/www

USER www-data

EXPOSE 9000
