#!/bin/sh
. `dirname ${0}`/site_conf.sh
echo Deleting all .pyc files
find ${WORK}/ -name '*.pyc' | xargs rm -f
#compile all the templates into the lib directory
echo compiling cheetah templates
export PYTHONPATH="${WORK}/lib"
/usr/bin/cheetah-compile -R --nobackup --idir ${WORK}/templates --odir ${WORK}/lib > /dev/null
echo deploying webware servlets, python modules, and data files
#webware servlets that actually get used in URLs on the site
rsync -a --delete "${WORK}/servlets/" "${APP}/${SITE}"
#python files used by servlets
rsync -a --delete "${WORK}/lib" "${APP}"
#data files used by servlets
rsync -a --delete "${WORK}/data" "${APP}"
echo overlaying webware config files
/bin/cp -ar "${WORK}/webware/"* "${APP}"
echo saving html for quasi-dynamic pages
pushd "${APP}" > /dev/null
./AppServer >> ${WORK}/AppServer.log 2>&1 &
for URL in home oberlin smartears bigclock
do
  wget -q "${STAGEURL}/app/${URL}" -O "${WORK}/static/${URL}.html"
done
#copy files into testing deployment structure
#static files for the web server: html, css, js, etc
echo copying static files to staging web server
rsync -a "${WORK}/static/" "${STATIC}"
echo cleaning up subversion, CVS, and editor files that are not needed
#delete source control repository metadata directories
for DIR in "${APP}" "${STATIC}"
do
    find "${DIR}" -name '.svn' -type d | xargs /bin/rm -rf
    find "${DIR}" -name 'CVS' -type d | xargs /bin/rm -rf
    find "${DIR}" -name \*~ -type f | xargs /bin/rm -f
done
popd > /dev/null
