#!/bin/sh
. `dirname ${0}`/site_conf.sh
rsync -e ssh -az "${STATIC}/" "${HOST}:${STATIC}"
rsync -e ssh -az  --exclude ErrorMsgs --exclude Cache --exclude Logs "${APP}/" "${HOST}:${APP}"
