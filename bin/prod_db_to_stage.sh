#!/bin/sh
. `dirname ${0}`/site_conf.sh
sudo service mysql stop
for DIR in persblog problog
do
    sudo rsync -aiEz --delete root@${HOST}:"/var/lib/mysql/${DIR}/" "/var/lib/mysql/${DIR}"
done
sudo service mysql start
