#!/bin/bash
#This script contains many install/deploy/run related tasks
#The tasks are defined as shell functions and prefixed with a namespace
#which is one of os, app, web, user, etc.
#The goal is that once you figure out how to clone the git repository,
#You can use this script to get a dev, staging, or production environment
#up and running.  The script doesn't do 100% automation yet but it does a lot.
#You run it like this:
#tasks.sh staging app:start
#where "staging" is the environment name (defined below) and app:start is the
#task name.
#The script can be run locally in which case the task's shell function is just
#directly executed, or against one or more remote hosts, in which case
#this script handles copying itself to the remote hosts then running itself
#on each host.

#To bootstrap a new staging host, you would run
#tasks.sh staging os:initial_setup
#tasks.sh staging user:initial_setup
#tasks.sh staging app:initial_setup
#tasks.sh staging os:init_scripts
#Then on the host run "sudo service node_peterlyons.com start" to start the app

TASK_SCRIPT="${0}"
export PATH=~/node/bin:$PATH

########## Define Environments ##########
SITE="peterlyons.com"
PRODUCTION_HOSTS="${SITE}"
STAGING_HOSTS="staging.${SITE}"
DEVURL="http://localhost:9400"
PRODURL="http://${SITE}"
REPO_URL="ssh://git.peterlyons.com/home/plyons/projects/peterlyons.com.git"
BRANCH="master"
NODE_VERSION="0.4.3"
PROJECT_DIR=~/projects/peterlyons.com
OVERLAY="${PROJECT_DIR}/overlay"
PUBLIC="${PROJECT_DIR}/public"
BRANCH=master
########## No-Op Test Tasks for sudo, root, and normal user ##########
#Use these to make sure your passwordless ssh is working, hosts are correct, etc
test:uptime() {
    uptime
}

test:uptime_sudo() { #TASK: sudo
    uptime
    id
}
########## OS Section ##########
#Wrapper function for getting everything in the OS bootstrapped
os:initial_setup() { #TASK: sudo
    os:prereqs
}


#These are the packages we need above and beyond the basic Ubuntu 10.10
#server default install.  There are prereqs included in that default config
#that we don't explicitly restate here (openssh-server, etc)
os:prereqs() { #TASK: sudo
    if ! which apt-get >/dev/null; then
        echo "apt-get not found in PATH.  Is this really an Ubuntu box?" \
            " Is your PATH correct?" 1>&2
        exit 5
    fi
    apt-get update
    cat <<EOF | grep -v "#" | sort | xargs apt-get --assume-yes install
#Needed to download node and npm
curl
#Needed to build node.js gem
g++
#Source Code Management
git-core
#Needed to build node.js with SSL support
libssl-dev
#Needed to build node.js
make
#For monitoring
monit
#For Wordpress blog
mysql-server
mysql-client
#We use perl in the tasks.sh script for quick command line file editing
perl
#This is our web server
nginx
#For Wordpress blog
php5-cgi
EOF
}

#Helper function for symlinking files in the git work area out into the OS
link() {
    if [ ! -h "${1}" ]; then
        ln -s "${OVERLAY}${1}" "${1}"
    fi
}

os:init_scripts() { #TASK: sudo
    [ -e /etc/nginx/sites-enabled/default ] && rm /etc/nginx/sites-enabled/default
    link "/etc/nginx/sites-enabled/${SITE}"
    link "/etc/monit/conf.d/php5-cgi_${SITE}.monitrc"
    link "/etc/monit/conf.d/nginx_${SITE}.monitrc"
    link "/etc/monit/conf.d/node_${SITE}.monitrc"
    link "/etc/monit/conf.d/mysql_${SITE}.monitrc"
    link "/etc/init.d/php5-cgi_${SITE}"
    link "/etc/init.d/node_${SITE}"
    cp "${OVERLAY}/etc/mysql/my.cnf" /etc/mysql/my.cnf
    cp "${OVERLAY}/etc/monit/monitrc" /etc/monit/monitrc
    update-rc.d "node_${SITE}" defaults
    update-rc.d "php5-cgi_${SITE}" defaults
    /etc/init.d/nginx reload
}

########## User Section ##########
#Wrapper function
user:initial_setup() {
    user:ssh_config
}


#The ForwardAgent configuration allows proxied ssh agent authentication
#So the capistrano scripts can run git+ssh commands on the app server
#and the end user's authentication will be proxied from the end user's
#desktop to the app server through to the git SCM host
user:ssh_config() {
    KEYS=~/.ssh/authorized_keys
    [ -d ~/.ssh ] || mkdir ~/.ssh
    touch "${KEYS}"
    #This is plyons's public SSH key
    if ! grep "^ssh-rsa AAAAB3NzaC1yc2EAAAABI" "${KEYS}" > /dev/null 2>&1; then
        cat <<EOF | tr -d '\n' >> "${KEYS}"
ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEArdBAo5yfb43w5/N3nxQvpDH6tCIvwvsJu/FrvRFiM8
+s/lGP0XxihzHYOJH/IdEz+WnjnKMBCWT/we3ZbWMFQ32yMzXAj2B+noaranIOLJ7C52uZrWoS2OOO
qWtwuj4jZLZ9v7cLvxC9v69b8dqyBOJG3YlIzqFQeYT7p4I1XWDRfwhsuX738zhvBYSx4w3tkZDmEp
sSl0+xNVjugBjNP81ynP3nUkeH+Ap2IUrJK5RnGpLXg+EX1DpPypXvn67SpHvz0+DgQuKwL+AYQdFS
86p21tuSDJ0yKz8CX+5nrJjjt2NUYgs0SwGU387UzqGFH5711C2rc9gkD6cvGbX0mQ
== zoot MacBook pro
EOF
    #Need a trailing newline
    echo >> "${KEYS}"
    fi
    touch ~/.ssh/config
    if ! grep "^Host git.peterlyons.com" ~/.ssh/config > /dev/null 2>&1; then
        cat <<EOF>> ~/.ssh/config
Host git.peterlyons.com
  ForwardAgent yes
EOF
    fi
}


########## Database (mysql) section ##########
db:prod_to_stage() {
    #If in there future there are more than 1 production host, just use the
    #first one in the list
    HOST=$(echo "${PRODUCTION_HOSTS}" | cut -d " " -f 1)
    for DB in persblog problog
    do
        FILE="/var/tmp/${DB}.bak.sql.bz2"
        #This does the production backup
        echo "Enter production password for user ${DB} and DB ${DB} when prompted"
        ssh -q -t "${HOST}" mysqldump --host localhost \
            --user "${DB}" --allow-keywords --add-drop-table --password \
            --add-drop-database --dump-date "${DB}" \| bzip2 -c \
            \> "${FILE}"
        #Copy the backup to the local computer
        scp -q "${HOST}:${FILE}" /var/tmp
        #Restore the backup locally
        echo "Enter staging password (twice) for user ${DB} and DB ${DB} when" \
            " prompted"
        bzcat "${FILE}" | mysql --host localhost --user "${DB}" --password "${DB}"
        #This updates the site URL, which must be relative for staging
        echo "update wp_options set option_value = '/${DB}' where option_name" \
            " in ('siteurl', 'home');" | mysql -u "${DB}" -p "${DB}"
        echo "Backup, transfer, restore, and tweak complete for ${DB}"
    done

}

########## Web (nginx) Section ##########
_web() {
    sudo /etc/init.d/nginx "${1}"
}

web:restart() {
     _web restart
}

web:reload() {
     _web reload
}

web:stop() {
    _web stop
}

web:start() {
    _web start
}

########## App (Node.js) Section ##########

#Helper functions
kill_stale() {
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
}

cdpd() {
    cd "${PROJECT_DIR}"
}

list_templates() {
    cdpd
    #We skip layout because it's just the layout and photos because
    #it's a dynamic page
    ls app/templates/*.{jade,md} | xargs -n 1 basename | sed -e s/\.jade// \
        -e /layout/d -e /photos/d -e /admin_galleries/d -e s/\.md//
}


app:initial_setup() {
    app:clone
    app:prereqs
}

app:clone() {
    PARENT="$(dirname ${PROJECT_DIR})"
    [ -d "${PARENT}" ] || mkdir -p "${PARENT}"
    cd "${PARENT}"
    git clone "${REPO_URL}"
    cd "${PROJECT_DIR}"
    git checkout "${BRANCH}"
    cd
}

app:prereqs() {
    cd "${PROJECT_DIR}"
    [ -d tmp ] || mkdir tmp
    cd tmp
    echo "Installing node.js version ${NODE_VERSION}"
    curl --silent --remote-name \
        "http://nodejs.org/dist/node-v${NODE_VERSION}.tar.gz"
    tar xzf node-v${NODE_VERSION}.tar.gz
    cd node-v${NODE_VERSION}
    ./configure  --prefix=~/node && make && make install && make && make install
    cd ..
    rm -rf node-*
    cd ..
    echo "Installing npm"
    #Yes, I know this is a security risk.  I accept the risk. Life is short.
    curl http://npmjs.org/install.sh | sh || exit 4
    echo "Installing npm packages"
    npm install
    echo "Here are the installed npm packages"
    npm ls
}

app:deploy() {
    cdpd
    echo "Deploying branch ${1-${BRANCH}}"
    git fetch origin --tags
    git checkout --track -b "${1-${BRANCH}}" || git checkout "${1-${BRANCH}}"
    git pull origin "${1-${BRANCH}}"
    sudo service node_peterlyons.com restart
}

app:test() {
  cdpd
  ./node_modules/.bin/coffee -c bin
  phantomjs ./bin/phantom_tests.js
  rm ./bin/phantom_tests.js
}

app:dev_start() {
    cdpd
    kill_stale
    PATH=./node_modules/.bin NODE_ENV=${1-dev} ./node_modules/.bin/supervisor -p server.coffee &
    echo "$!" > "${PID_FILE}"
    echo "new node process started with pid $(cat ${PID_FILE})"
    if [ $(uname) == "Darwin" ]; then
        sleep 1
        #open -a "Firefox" "http://localhost:$(./node_modules/.bin/coffee bin/get_port.coffee)${1}"
    fi
}

app:dev_stop() {
    cdpd
    kill_stale
}

app:debug() {
  cdpd
  kill_stale
  ./node_modules/.bin/coffee --nodejs --debug server.coffee
}

app:build_static() {
    echo "Generating HTML for static templated pages from ${DEVURL}..."
    for URI in $(list_templates) leveling_up
    do
        URL="${DEVURL}/${URI}"
        echo -n "${URI}, "
        EXIT_CODE=0
        curl --silent "${URL}" --output \
            "${PUBLIC}/${URI}.html" || EXIT_CODE=$?
        if [ ${EXIT_CODE} -ne 0 ]; then
            echo "FAILED to retrieve ${URL}"
            exit ${EXIT_CODE}
        fi
    done
    echo "header_boilerplate.php"
    curl --silent "${DEVURL}/home?wordpress=1" | \
        sed '/WORDPRESS HEADER BOILERPLATE/d' > \
        "${PUBLIC}/persblog/wp-content/themes/fluid-blue/header_boilerplate.php"
}

app:prod_release() {
    echo "Performing a production peterlyons.com release"
    eval $(ssh-agent -s) && ssh-add
    git checkout develop
    git pull origin develop
    #BUGBUG#Disabling#jasbin || exit 5
    echo "Current version is $(cat version.txt)"
    echo -n "New version: "
    read NEW_VERSION
    git checkout -b "release-${NEW_VERSION}" develop
    echo "${NEW_VERSION}" > version.txt
    git commit -a -m "Bumped version number to ${NEW_VERSION}"
    echo "ABOUT TO MERGE INTO MASTER. CTRL-C now to abort. ENTER to proceed."
    read DONTCARE
    git checkout master
    git merge --no-ff "release-${NEW_VERSION}"
    echo "Now type notes for the new tag"
    git tag -a "v${NEW_VERSION}"
    git checkout develop
    git merge --no-ff "release-${NEW_VERSION}"
    git branch -d "release-${NEW_VERSION}"
    git push origin develop
    git checkout master
    git push origin master
    git checkout develop #Not good form to leave master checked out
    echo "Ready to go. Type './bin/tasks.sh production app:deploy' to push to production"
}

app:validate() {
    echo "Validating HTML: "
    local ERRORS=0
    local TMP=photos_tmp
    BASE="${DEVURL}"
    EXT=""
    if [ "${1}" == "production" ]; then
        BASE="${PRODURL}"
        EXT=".html"
        echo "Validating the PRODUCTION site"
    fi
    for URI in $(list_templates) app/photos
    do
        printf '  %-25s' "${URI}: "
        local TMP_HTML="/tmp/tmp_html.$$.html"
        local FETCH_EC=0
        curl --silent "${BASE}/${URI}${EXT}" --output "${TMP_HTML}" || \
            FETCH_EC=$?
        if [ ${FETCH_EC} -eq 7 ]; then
            echo "SERVER IS NOT RUNNING. ABORTING."
            exit ${FETCH_EC}
        fi
        if [ ${FETCH_EC} -ne 0 ]; then
            echo "FAILED (${FETCH_EC}"
            ERRORS=$((ERRORS + 1))
            continue
        fi
        local VALID_EC=0
        curl --silent "http://validator.w3.org/check" --form \
            "fragment=<${TMP_HTML}" | \
            egrep "was successfully checked as" > /dev/null || VALID_EC=$?
        if [ ${VALID_EC} -ne 0 ]; then
            echo "INVALID"
            ERRORS=$((ERRORS + 1))
        else
            echo "valid"
        fi
        rm "${TMP_HTML}"
    done
    if [ ${ERRORS} -ne 0 ]; then
        echo "ERROR: ${ERRORS} documents are invalid" 1>&2
        exit 5
    else
        echo "SUCCESS: All documents successfully validated"
    fi
}

if ! expr "${1}" : '.*:' > /dev/null; then
    ENV_NAME="${1}"
    shift
    OP="${1}"
    shift
    case "${ENV_NAME}" in
        staging)
            HOSTS="${STAGING_HOSTS}"
        ;;

        production)
            HOSTS="${PRODUCTION_HOSTS}"
        ;;
    esac
else
    OP="${1}"
    shift
fi

case "${OP}" in
    app:*|db:*|os:*|test:*|user:*|web:*)
        #Op looks valid-ish
    ;;
    *)
        echo "ERROR: unknown task ${OP}" 1>&2
        exit 1
    ;;
esac

#figure out sudo
if egrep "^${OP}\(\).*#TASK: sudo" "${TASK_SCRIPT}" > /dev/null; then
    SUDO=sudo
fi

if [ -z "${ENV_NAME}" ]; then
    #local mode
    eval "${OP}" "${@}"
else
    #remote mode
    for HOST in ${HOSTS}
    do
        echo "Running task ${OP} on ${HOST} as ${SUDO-$USER}"
        scp "${TASK_SCRIPT}" "${HOST}:/tmp"
        ssh -q -t "${HOST}" "${SUDO}" bash  \
            "/tmp/$(basename ${TASK_SCRIPT})" "${OP}" "${@}"
    done
fi
