#!/bin/bash
SITE="peterlyons.com"
WORK=~plyons/projects/${SITE}
STATIC="overlay/var/www/${SITE}"
RSYNC_CMD="rsync -caiE"
HOST="hubb.peterlyons.com"
DEVURL="http://localhost:9400"
STAGEURL="http://staging.${SITE}"
PRODURL="http://${SITE}"

