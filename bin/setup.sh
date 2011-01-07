#!/bin/sh
SITE=peterlyons.com
cd $(dirname "${0}")/../overlay
OVERLAY=$(pwd)
link() {
    if [ ! -h "${1}" ]; then
        ln -s "${OVERLAY}${1}" "${1}"
    fi
}
link "/etc/monit/conf.d/${SITE}.monitrc"
cp "${OVERLAY}/etc/monit/monitrc" /etc/monit/monitrc
