#!/bin/sh
. `dirname ${0}`/site_conf.sh
echo Deleting all .pyc files
find ${WORK}/ -name '*.pyc' | xargs rm -f
#compile all the templates into the lib directory
echo compiling cheetah templates
/usr/bin/env PYTHONPATH="${WORK}/overlay${APP}/lib" cheetah-compile -R --nobackup --idir "${WORK}/templates" --odir "${WORK}/overlay${APP}/lib" > /dev/null
echo Setting owner/perms on files in work area
sudo chgrp -R webadmin "${WORK}/overlay"
find "${WORK}" -type f -print0 | xargs -0 chmod 664
find "${WORK}" -type d -print0 | xargs -0 chmod 775
chmod 775 "${WORK}"/overlay/etc/init.d/*
chmod 775 "${WORK}"/overlay${APP}/AppServer
#chmod 775 "${WORK}"/overlay/var/www/${SITE}/*.cgi
chmod 775 "${WORK}"/bin/*.sh
chmod 775 "${WORK}"/bin/*.py
#chmod 777 "${WORK}"/overlay/var/www/${SITE}/*blog/wp-content/cache
for DIR in "${STATIC}" "${APP}"
do
    if [ ! -w "${DIR}" ]; then
        echo "directory "${DIR}" doesn't exist. Creating it."
        sudo mkdir -p "${DIR}"
        sudo chown plyons:webadmin "${DIR}"
        sudo chmod g+w "${DIR}"
    fi
done
echo overlaying flat files into the filesystem
#NOTA BENE the trailing slashes in the rsync commands below are important
rsync -aiE --delete --exclude-from="${WORK}/bin/exclude_static.txt" "${WORK}/overlay${STATIC}/" "${STATIC}"
rsync -aiE --delete --exclude-from="${WORK}/bin/exclude_app.txt" "--exclude=*.pyc" "${WORK}/overlay${APP}/" "${APP}"
for DIR in /etc/init.d /etc/nginx /etc/nginx/sites-available /etc/nginx/sites-enabled
do
    if [ ! -w "${DIR}" ]; then
        echo "Setting up ${DIR} with correct permissions"
        sudo mkdir -p "${DIR}"
        sudo chgrp webadmin "${DIR}"
        sudo chmod g+w "${DIR}"
    fi
done
rsync -rliE --exclude-from="${WORK}/bin/exclude_etc.txt" "${WORK}/overlay/etc/" /etc
perl -pi -e "s/server_name.*/server_name stage.${SITE};/" "/etc/nginx/sites-available/${SITE}"
echo saving html for quasi-dynamic pages
if [ ! -f "/etc/init.d/webware_${SITE}" ]; then
    sudo chgrp webadmin /etc/init.d
    sudo chmod g+w /etc/init.d
fi
"/etc/init.d/webware_${SITE}" restart
sleep 1
for URI in `ls ${WORK}/templates/*.tmpl | xargs -n 1 basename | sed -e s/_tmpl.tmpl// | sed -e /photos/d -e /main/d`
do
    URL="${STAGEURL}/app/${URI}"
    cat << EOF > "${WORK}/overlay${APP}/${SITE}/${URI}.py"
from baseservlet import baseservlet
class ${URI}(baseservlet):
    pass
EOF
    echo retrieving HTML for "${URL}"
    wget -q "${URL}" -O "${WORK}/overlay${STATIC}/${URI}.html"
    if [ "${?}" -eq "0" ]; then
        cp "${WORK}/overlay${STATIC}/${URI}.html" "${STATIC}"
    else
        echo "FAILED to retrieve ${URL}"
    fi 
done
#This creates a version of the main header suitable for embedding the blogs
wget -q "${STAGEURL}/app/home?wordpress=1" -O "${WORK}/overlay${STATIC}/persblog/wp-content/themes/fluid-blue/header_boilerplate.php"
sudo chgrp webadmin "${STATIC}"/*.html
echo setting permissions, cleaning up SCM and editor files that are not needed
#delete source control repository metadata directories
for DIR in "${APP}" "${STATIC}"
do
    find "${DIR}" -name .svn -type d -print0 | xargs -0 /bin/rm -rf
    find "${DIR}" -name CVS -type d -print0 | xargs -0 /bin/rm -rf
    find "${DIR}" -type f -name \*~ -o -name .gitignore -print0 | xargs -0 /bin/rm -f
done
