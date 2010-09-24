#!/bin/sh
. `dirname ${0}`/site_conf.sh
#The ${1} below allows the user to pass an "n" argument to do a preview
rsync -aiEz${1} "--exclude-from=${WORK}/bin/exclude_static.txt" "${STATIC}/" "${HOST}:${STATIC}"
rsync -aiEz${1} "--exclude-from=${WORK}/bin/exclude_app.txt" "${APP}/" "${HOST}:${APP}"
rsync -iEz${1} /etc/init.d/webware_peterlyons.com /etc/init.d/spawn-fcgi_peterlyons.com "${HOST}:/etc/init.d"
rsync -iEz${1} /etc/init.d/webware_peterlyons.com /etc/init.d/spawn-fcgi_peterlyons.com "${HOST}:/etc/init.d"
rsync -iEz${1} "${WORK}/overlay/etc/nginx/sites-available/peterlyons.com" "${HOST}:/etc/nginx/sites-available"
