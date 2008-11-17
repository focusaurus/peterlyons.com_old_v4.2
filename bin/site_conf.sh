#!/bin/bash
SITE="peterlyons.com"
WORK="${HOME}/projects/${SITE}"
APP="/var/www/webware/${SITE}"
STATIC="/var/www/${SITE}"
RSYNC_CMD="rsync -e ssh -av"
HOST="bean.peterlyons.com"
STAGEURL="http://stage.peterlyons.com"
