FROM nginx:1.23

ADD ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf
ADD ./docker/nginx/.htpasswd /etc/nginx/.htpasswd

COPY --chown=www-data:www-data ./web/assets /var/www/web/assets

EXPOSE 80
