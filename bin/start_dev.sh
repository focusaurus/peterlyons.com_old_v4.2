#!/bin/bash
cd "$(dirname ${0})/.."
DIR=$(pwd)
source "${DIR}/bin/site_conf.sh"

PID_FILE="${DIR}/tmp/server.pid"
PID_DIR="$(dirname ${PID_FILE})"
if [ ! -d "${PID_DIR}" ]; then
    mkdir "${PID_DIR}"
fi
if [ -f "${PID_FILE}" ]; then
    PID=$(cat "${PID_FILE}")
    if ps -p "${PID}" > /dev/null; then
        echo "killing old node server process $(cat ${PID_FILE})"
        kill "${PID}"
        rm "${PID_FILE}"
    fi
fi
NODE_ENV=test coffee server.coffee &
echo "$!" > "${PID_FILE}"
echo "new node process started with pid $(cat ${PID_FILE})"
if [ $(uname) == "Darwin" ]; then
    sleep 1
    open -a "Google Chrome" "http://localhost:$(coffee bin/get_port.coffee)"
fi
