#!/bin/sh

### BEGIN INIT INFO
# Provides:          moinmoin_peterlyons.com
# Required-Start:    $all
# Required-Stop:     $all
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: starts MoinMoin standalone wiki server
# Description:       starts MoinMoin standalone wiki server using start-stop-daemon
### END INIT INFO
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
NAME=moinmoin_peterlyons.com
PID=/var/run/moinmoin_peterlyons.com.pid
DAEMON=/usr/share/moin/server/moin
DAEMON_OPTS="server standalone --config-dir /var/local/plwiki"

test -x $DAEMON || exit 0

set -e

start() {
    echo "Starting $NAME: "
    start-stop-daemon --start --pidfile $PID --make-pidfile --background --exec $DAEMON -- $DAEMON_OPTS
    echo "done."
}

stop() {
   echo "Stopping $NAME: "
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
