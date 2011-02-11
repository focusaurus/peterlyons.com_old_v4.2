#!/bin/sh
. $(dirname ${0})/site_conf.sh

list_templates() {
    ls ${WORK}/${APP}/templates/*.coffee | xargs -n 1 basename | sed -e s/\.coffee// \
        | sed -e /layout/d
}

echo Compiling CoffeeScript to JavaScript
coffee --compile --output "${WORK}/${APP}" "${WORK}/app.coffee" || exit 5
#for URI in $(list_templates)
#do
#    coffee --compile --output "${WORK}/${APP}" "${WORK}/templates/${URI}.coffee"
#done
cd "${WORK}/${APP}"
if [ -f app.pid ]; then
    PID=$(cat app.pid)
    echo "Checking for already running PID ${PID}"
    if ps -p "${PID}" > /dev/null; then
        kill -9 "${PID}"
        rm app.pid
    fi
fi         
node app.js &
echo $! > app.pid
sleep 1
echo "Generating HTML for static templated pages from ${DEVURL}..."
for URI in $(list_templates)
do
    URL="${DEVURL}/${URI}"
    echo -n "${URI}, "
    EXIT_CODE=0
    curl --silent "${URL}" --output "${WORK}/${STATIC}/${URI}.html" || EXIT_CODE=$?
    if [ ${EXIT_CODE} -ne 0 ]; then
        echo "FAILED to retrieve ${URL}"
        exit ${EXIT_CODE}
    fi 
done
echo
echo "To stop node, run"
echo "kill -9 $(cat app.pid)"
