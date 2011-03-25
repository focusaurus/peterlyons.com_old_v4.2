#!/bin/sh
cd "$(dirname ${0})/.."
DIR=$(pwd)
source "${DIR}/bin/site_conf.sh"

list_templates() {
    ls ${WORK}/app/templates/*.jade | xargs -n 1 basename | sed -e s/\.jade// \
        | sed -e /layout/d | sed -e /photos/d
}

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
