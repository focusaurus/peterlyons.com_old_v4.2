#!/bin/sh
. `dirname ${0}`/site_conf.sh
#The ${1} below allows the user to pass an "n" argument to do a preview
rsync -e ssh -az${1} --exclude php.html "${STATIC}/" "${HOST}:${STATIC}"
rsync -e ssh -az${1}  --exclude ErrorMsgs --exclude Cache --exclude Logs --exclude appserverpid.txt "${APP}/" "${HOST}:${APP}"
