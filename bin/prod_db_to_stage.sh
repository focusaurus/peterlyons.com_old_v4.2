#!/bin/sh
. `dirname ${0}`/site_conf.sh
sudo service mysql stop
for DIR in persblog problog
do
    sudo rsync -aiEz --delete root@${HOST}:"/var/lib/mysql/${DIR}/" "/var/lib/mysql/${DIR}"
done
sudo service mysql start
#Now we must update the site URL
echo "ENTER THE PROBLOG DB password"
echo "update wp_options set option_value = '/problog' where option_name in ('siteurl', 'home');" | mysql -u problog -p problog

echo "ENTER THE PERSBLOG DB password"
echo "update wp_options set option_value = '/persblog' where option_name in ('siteurl', 'home');" | mysql -u persblog -p persblog
