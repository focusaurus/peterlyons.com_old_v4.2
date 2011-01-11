#!/bin/sh
SITE=peterlyons.com
cd $(dirname "${0}")/../overlay
OVERLAY=$(pwd)
link() {
    if [ ! -h "${1}" ]; then
        ln -s "${OVERLAY}${1}" "${1}"
    fi
}
link "/etc/monit/conf.d/spawn-fcgi_${SITE}.monitrc"
link "/etc/monit/conf.d/nginx_${SITE}.monitrc"
link "/etc/monit/conf.d/webware_${SITE}.monitrc"
#link "/etc/mysql/my.cnf"
#link "/etc/mysql/debian-start"
#link "/etc/init/mysql.conf"
cp "${OVERLAY}/etc/monit/monitrc" /etc/monit/monitrc
