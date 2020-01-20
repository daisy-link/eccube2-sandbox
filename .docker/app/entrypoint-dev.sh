#!/bin/sh

if [ -n "${HOST_UID}" -a "${HOST_UID}" != "$(id -u www-data)" ]; then
    usermod -u "${HOST_UID}" www-data
fi

if [ -n "${HOST_GID}" -a "${HOST_GID}" != "$(id -g www-data)" ]; then
    groupmod -g "${HOST_GID}" www-data
fi

ini=$(cat << EOS
xdebug.remote_host=${XDEBUG_HOST:-$(ip route | awk 'NR==1 {print $3}')}
xdebug.remote_port=${XDEBUG_PORT}
EOS
)
echo "${ini}" >> /usr/local/etc/php/conf.d/dev.ini

exec /entrypoint-base.sh "$@"
