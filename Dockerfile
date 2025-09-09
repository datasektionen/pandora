FROM alpine:3.16

RUN apk --no-cache add php8 php8-mbstring php8-pdo php8-openssl php8-json php8-phar php8-fileinfo \
    php8-dom php8-tokenizer php8-xml php8-xmlwriter php8-session php8-pgsql php8-pdo_pgsql php8-fpm php8-curl \
    git zip nginx

RUN php -r 'copy("https://getcomposer.org/installer", "php://stdout");' | \
    php -- --install-dir=/usr/local/bin --filename=composer --quiet

COPY nginx_app.conf /etc/nginx/http.d/default.conf
RUN sed -i 's/;clear_env/clear_env/' /etc/php8/php-fpm.d/www.conf # Gives PHP access to environment variables

RUN : \
    && addgroup -S www \
    && adduser -D -H -G www www \
    && chown www:www /var/log/php8 \
    && :

WORKDIR /app
COPY . /app

# With cache, build goes brrrrrr
# Unless specifying `--no-scripts`, someone thought it would be funny to connect to the database
# right after installing dependencies.
RUN --mount=type=cache,target=/app/vendor/ composer install --no-scripts --no-dev && cp -r vendor _vendor
RUN ln -s _vendor vendor && chown -R www:www /app

EXPOSE 8000

USER root
ENTRYPOINT ["/app/run.sh"]
