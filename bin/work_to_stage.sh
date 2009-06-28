#!/bin/sh
. `dirname ${0}`/site_conf.sh
echo Deleting all .pyc files
find ${WORK}/ -name '*.pyc' | xargs rm -f
#compile all the templates into the lib directory
echo compiling cheetah templates
/usr/bin/env PYTHONPATH="${WORK}/overlay${APP}/lib" cheetah-compile -R --nobackup --idir "${WORK}/templates" --odir "${WORK}/overlay${APP}/lib" > /dev/null
echo Setting owner/perms on files in work area
chgrp -R webadmin "${WORK}/overlay"
find "${WORK}" -type f -print0 | xargs -0 chmod 664
find "${WORK}" -type d -print0 | xargs -0 chmod 775
chmod 775 "${WORK}"/overlay/etc/init.d/*
chmod 775 "${WORK}"/overlay${APP}/AppServer
chmod 775 "${WORK}"/bin/*.sh
echo overlaying flat files into the filesystem
#NOTA BENE the trailing slashes in the rsync commands below are important
rsync -aiE --delete --exclude-from="${WORK}/bin/exclude_static.txt" "${WORK}/overlay${STATIC}/" "${STATIC}"
rsync -aiE --delete --exclude-from="${WORK}/bin/exclude_app.txt" "--exclude=*.pyc" "${WORK}/overlay${APP}/" "${APP}"
rsync -rliE "${WORK}/overlay/etc/" /etc
perl -pi -e "s/ServerName.*/ServerName stage.${SITE}/" "/etc/apache2/sites-available/${SITE}"
echo saving html for quasi-dynamic pages
"/etc/init.d/webware_${SITE}" restart
for URL in `ls ${WORK}/templates/*.tmpl|xargs -n 1 basename|sed -e s/_tmpl.tmpl//|grep -v photos`
#for URL in `ls ${WORK}/templates/*.tmpl|grep -v photos`
#home oberlin smartears bigclock code_conventions
do
    wget -q "${STAGEURL}/app/${URL}" -O "${WORK}/overlay${STATIC}/${URL}.html"
    cp "${WORK}/overlay${STATIC}/${URL}.html" "${STATIC}"
done
chgrp webadmin "${STATIC}"/*.html
echo setting permissions, cleaning up SCM and editor files that are not needed
#delete source control repository metadata directories
for DIR in "${APP}" "${STATIC}"
do
    find "${DIR}" -name .svn -type d -print0 | xargs -0 /bin/rm -rf
    find "${DIR}" -name CVS -type d -print0 | xargs -0 /bin/rm -rf
    find "${DIR}" -type f -name \*~ -o -name .gitignore -print0 | xargs -0 /bin/rm -f
done
