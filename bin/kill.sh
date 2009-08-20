#!/bin/sh
. `dirname ${0}`/site_conf.sh
/etc/init.d/webware_"${SITE}" stop
