#!/bin/sh
. `dirname ${0}`/site_conf.sh

find ${WORK}/ -name '*.pyc' | xargs rm -f
#compile all the templates into the lib directory
export PYTHONPATH="${WORK}/lib"
/usr/bin/cheetah-compile -R --nobackup --idir ${WORK}/templates --odir ${APP}/lib > /dev/null
#webware servlets that actually get used in URLs on the site
/bin/cp -rf "${WORK}/servlets/"* "${APP}/${SITE}"
#python files used by servlets
/bin/cp -rf "${WORK}/lib" "${APP}"
#data files used by servlets
/bin/cp -rf "${WORK}/data" "${APP}"
#delete source control repository metadata directories
for DIR in "${APP}" "${STATIC}"
do
    find "${DIR}" -name '.svn' -type d | xargs rm -rf
    find "${DIR}" -name 'CVS' -type d | xargs rm -rf
    find "${DIR}" -name \*~ -type f | xargs rm -f
done
pushd "${APP}" > /dev/null
./AppServer >> ${WORK}/AppServer.log 2>&1 &
for URL in home oberlin smartears bigclock
do
  wget -q "${STAGEURL}/app/${URL}" -O "${WORK}/static/${URL}.html"
done
#copy files into testing deployment structure
#static files for the web server: html, css, js, etc
/bin/cp -rf ${WORK}/static/* ${STATIC}
popd > /dev/null
