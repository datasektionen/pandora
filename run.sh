#!/bin/sh

sed -i "s/LISTEN_PORT/$PORT/g" /etc/nginx/http.d/default.conf

su www -c 'php artisan config:cache'
su www -c 'php artisan migrate --force'

su www -c 'php-fpm8 -F' &
nginx -g 'daemon off;' &

wait -n
exit $?

# This scripts starts both php-fpm and nginx. If one exits, it kills the other
# one and exits, stopping the container (which could then be restarted)
