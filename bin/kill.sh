#!/bin/sh
. `dirname ${0}`/site_conf.sh
kill `cat "${APP}/appserverpid.txt"`
