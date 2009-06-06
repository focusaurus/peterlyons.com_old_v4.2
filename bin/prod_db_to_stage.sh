#!/bin/sh
. `dirname ${0}`/site_conf.sh
sudo /etc/init.d/mysql stop
for DIR in persblog problog
do
    sudo rsync -aiEz --delete root@${HOST}:"/var/lib/mysql/${DIR}/" "/var/lib/mysql/${DIR}"
done
sudo /etc/init.d/mysql start
