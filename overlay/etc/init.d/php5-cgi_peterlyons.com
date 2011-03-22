#!/bin/sh

### BEGIN INIT INFO
# Provides:          php5-cgi_peterlyons.com
# Required-Start:    $all
# Required-Stop:     $all
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: starts FastCGI for PHP
# Description:       starts FastCGI for PHP using start-stop-daemon
### END INIT INFO
PATH=/sbin:/bin:/usr/sbin:/usr/bin
NAME=php5-cgi_peterlyons.com
PID=/var/run/php5-cgi_peterlyons.com.pid
DAEMON_OPTS="PATH=/usr/bin PHP_FCGI_CHILDREN=2 PHP_FCGI_MAX_REQUESTS=1000 /usr/bin/php5-cgi -b 127.0.0.1:9200"

test -x /usr/bin/env || exit 0

set -e

start() {
    echo "Starting $NAME: "
    start-stop-daemon --start --pidfile $PID --make-pidfile --user www-data \
        --group www-data --background --exec /usr/bin/env -- $DAEMON_OPTS
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
