#!/bin/sh
SITE="peterlyons.com"
DIR="/var/www/webware/${SITE}"

getrunningpid () {
    PIDFILE="${DIR}/appserver.pid"
    if [ -f "${PIDFILE}" ]; then
        PID=$((`head -1 "${PIDFILE}" | grep "[0-9]"`))
        #check if it is running
        kill -0 "${PID}"
        if [ ${?} -eq 0 ]; then
            return ${PID}
        fi
    fi
    return 0
}

start () {
    getrunningpid
    PID=$?
    if [ ${PID} -gt 0 ]; then
        printf "Webware for ${SITE} already running with PID ${PID}\n"
        return
    fi
    printf "Starting Webware"
    CMD="(cd ${DIR} && nohup ./AppServer -l lib -w /usr/local/webware > /dev/null 2>&1 &)"
    if [ `id -u` -eq 0 ]; then
        printf " (as user plyons)"
        su - plyons -c "${CMD}"
    else
        eval "${CMD}"
    fi
    printf ": ${SITE}.\n"
    unset CMD
}

stop () {
    getrunningpid
    PID=$?
    if [ ${PID} -gt 0 ]; then
        printf "Stopping Webware: ${SITE} at PID ${PID}\n"
        kill "${PID}"
        return
    else
        printf "Webware for ${SITE} not running (No file ${PIDFILE} or no matching process)\n"
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
        #Allow time for process to stop cleanly and fully
        sleep 2
        start
    ;;
    *)
        echo "Usage: /etc/init.d/`basename ${0}` {start|stop|restart}"
        exit 1
    ;;
esac
