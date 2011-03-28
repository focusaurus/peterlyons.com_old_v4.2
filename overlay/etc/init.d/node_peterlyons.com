#!/bin/sh

### BEGIN INIT INFO
# Provides:          node_peterlyons.com
# Required-Start:    $all
# Required-Stop:     $all
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: starts node.js application server
# Description:       starts node.js application server using start-stop-daemon
### END INIT INFO
PATH=/home/plyons/node/bin:/sbin:/bin:/usr/sbin:/usr/bin
NAME=node_peterlyons.com
PID=/var/run/node_peterlyons.com.pid
DAEMON_OPTS="PATH=/home/plyons/node/bin:/usr/bin NODE_ENV=production /home/plyons/node/bin/coffee server.coffee"
PROJECT_DIR=/home/plyons/projects/peterlyons.com

test -x /usr/bin/env || exit 0

set -e

start() {
    echo -n "Starting $NAME: "
    start-stop-daemon --start --pidfile $PID --make-pidfile --user www-data \
        --group www-data --chdir ${PROJECT_DIR} --background \
        --exec /usr/bin/env -- $DAEMON_OPTS
    echo "done."
}

stop() {
   echo -n "Stopping $NAME: "
    start-stop-daemon --stop  --pidfile $PID --retry 5
    rm -f $PID
    echo "done."
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
        echo "Usage: /etc/init.d/$NAME {start|stop|restart}" >&2
        exit 1
        ;;
esac
