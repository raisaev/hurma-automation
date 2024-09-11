FROM nginx:1.27

ADD ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf
ADD ./docker/nginx/.htpasswd /etc/nginx/.htpasswd

COPY --chown=www-data:www-data ./public/assets /var/www/public/assets

EXPOSE 80
