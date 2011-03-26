#!/bin/bash
TASK_SCRIPT="${0}"
export PATH=~/node/bin:$PATH

########## Define Environments ##########
SITE="peterlyons.com"
PRODUCTION_HOSTS="peterlyons.com"
STAGING_HOSTS="10.11.12.104"
REPO_URL="ssh://git.peterlyons.com/home/plyons/projects/peterlyons.com"
BRANCH="node_express_coffeescript"
NODE_VERSION="0.4.3"
PROJECT_DIR=~/projects/peterlyons.com
OVERLAY="${PROJECT_DIR}/overlay"

########## No-Op Test Tasks for sudo, root, and normal user ##########
test:uptime() {
    uptime
}

test:uptime_sudo() { #TASK: sudo
    uptime
    id
}
########## OS Section ##########
link() {
    if [ ! -h "${1}" ]; then
        ln -s "${OVERLAY}${1}" "${1}"
    fi
}

#Wrapper function for getting everything in the OS bootstrapped
os:initial_setup() { #TASK:sudo
    os:prereqs
}


#These are the packages we need above and beyond the basic Ubuntu 10.10
#server default install.  There are prereqs included in that default config
#that we don't explicitly restate here (openssh-server, etc)
os:prereqs() { #TASK:sudo
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
#Needed for get_prereqs (will normally always be available on Ubuntu anyway)
python
#This is our web server
nginx
#For Wordpress blog
php5-cgi
EOF
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
    KEYS=~/.ssh/authorized_keys2
    [ ! -d ~/.ssh ] || mkdir ~/.ssh
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
    if ! grep "^Host git.peterlyons.com" ~/.ssh/config > /dev/null 2>&1; then
        cat <<EOF>> ~/.ssh/config
Host git.peterlyons.com
  ForwardAgent yes
EOF
    fi
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
    ls app/templates/*.jade | xargs -n 1 basename | sed -e s/\.jade// \
        | sed -e /layout/d | sed -e /photos/d
}


app:initial_setup() {
    #app:clone
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
    #echo "Installing npm"
    #curl http://npmjs.org/install.sh | sh || exit 4
    for DEP in $(python "./bin/get_prereqs.py")
    do
        npm install "${DEP}" || exit 5
    done
}
app:start() {
    cdpd
    kill_stale
    NODE_ENV=${1-test} coffee server.coffee &
    echo "$!" > "${PID_FILE}"
    echo "new node process started with pid $(cat ${PID_FILE})"
    if [ $(uname) == "Darwin" ]; then
        sleep 1
        open -a "Google Chrome" "http://localhost:$(coffee bin/get_port.coffee)"
    fi
}

app:stop() {
    cdpd
    kill_stale
}

app:build_static() {
    echo "Generating HTML for static templated pages from ${DEVURL}..."
    for URI in $(list_templates)
    do
        URL="${DEVURL}/${URI}"
        echo -n "${URI}, "
        EXIT_CODE=0
        curl --silent "${URL}" --output \
            "${WORK}/${STATIC}/${URI}.html" || EXIT_CODE=$?
        if [ ${EXIT_CODE} -ne 0 ]; then
            echo "FAILED to retrieve ${URL}"
            exit ${EXIT_CODE}
        fi
    done
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
    app:*|os:*|test:*|user:*|web:*)
        #Op looks valid-ish
    ;;
    *)
        echo "ERROR: unknown task ${OP}" 1>&2
        exit 1
    ;;
esac

#figure out sudo
if egrep "^${OP}\(\).*#TASK:\s*sudo" "${TASK_SCRIPT}" > /dev/null; then
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
        ssh -t "${HOST}" "${SUDO}" bash  \
            "/tmp/$(basename ${TASK_SCRIPT})" "${OP}" "${@}"
    done
fi
