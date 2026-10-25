FROM alpine:3.24

RUN apk --no-cache add php83 php83-mbstring php83-pdo php83-openssl php83-json php83-phar php83-fileinfo \
    php83-dom php83-tokenizer php83-xml php83-xmlwriter php83-session php83-pgsql php83-pdo_pgsql php83-fpm php83-curl \
    git zip nginx && ln -s /usr/bin/php83 /usr/bin/php

RUN apk add --no-cache postgresql16-client

RUN php83 -r 'copy("https://getcomposer.org/installer", "php://stdout");' | \
    php83 -- --install-dir=/usr/local/bin --filename=composer --quiet

COPY nginx_app.conf /etc/nginx/http.d/default.conf
RUN sed -i 's/;clear_env/clear_env/' /etc/php83/php-fpm.d/www.conf # Gives PHP access to environment variables

RUN : \
    && addgroup -S www \
    && adduser -D -H -G www www \
    && chown www:www /var/log/php83 \
    && :

WORKDIR /app
COPY . /app

# With cache, build goes brrrrrr
# Unless specifying `--no-scripts`, someone thought it would be funny to connect to the database
# right after installing dependencies.
RUN --mount=type=cache,target=/app/vendor/ composer install --no-scripts --no-dev && cp -r vendor _vendor
RUN ln -s _vendor vendor && chown -R www:www /app

EXPOSE 8000

RUN touch /app/storage/logs/laravel.log && chown www:www /app/storage/logs/laravel.log

USER root
ENTRYPOINT ["/app/run.sh"]
