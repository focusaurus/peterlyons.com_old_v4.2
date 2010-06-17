#!/bin/sh

### BEGIN INIT INFO
# Provides:          spawn-fcgi_peterlyons.com
# Required-Start:    $all
# Required-Stop:     $all
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: starts FastCGI for PHP
# Description:       starts FastCGI for PHP using start-stop-daemon
### END INIT INFO
#/usr/bin/spawn-fcgi -f /usr/bin/php-cgi -u www-data -g www-data -a 127.0.0.1 -p 9100 -P /var/run/fastcgi-php-peterlyons.com.pid
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
NAME=spawn-fcgi_peterlyons.com
PID=/var/run/spawn-fcgi_peterlyons.com.pid
DAEMON=/usr/bin/spawn-fcgi
DAEMON_OPTS="-f /usr/bin/php-cgi -a 127.0.0.1 -p 9200 -u www-data -g www-data -P $PID"

test -x $DAEMON || exit 0

set -e

start() {
    echo "Starting $NAME: "
    start-stop-daemon --start --pidfile $PID --exec $DAEMON -- $DAEMON_OPTS
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
