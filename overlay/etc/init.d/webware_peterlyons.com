#!/bin/sh
SITE="peterlyons.com"
DIR="/var/www/webware/${SITE}"
CMD="cd ${DIR};nohup ./AppServer -l lib -w /usr/local/webware > /dev/null &"
start () {
    printf "Starting Webware: "
    if [ "`id -u`" = "0" ]; then
        su - plyons -c "${CMD}"
    else
        eval "${CMD}"
    fi
    printf "${SITE}.\n"
}

stop () {
    PID="${DIR}/appserver.pid"
    if [ -f "${PID}" ]; then
        printf "Stopping Webware: "
        kill `cat ${PID}`
        printf "${SITE}.\n"
    else
        printf "Webware for ${SITE} not running (No file ${PID})\n"
    fi
}

case "$1" in
  start)
    start
    ;;
  stop)
    stop
    ;;
  restart)
    stop
    start
    ;;
  *)
    echo "Usage: /etc/init.d/`basename ${0}` {start|stop|restart}"
    exit 1
    ;;
esac
