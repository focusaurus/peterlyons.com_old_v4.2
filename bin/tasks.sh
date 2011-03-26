#!/bin/bash
#cd "$(dirname ${0})/.."
#DIR=$(pwd)
#source "${DIR}/bin/site_conf.sh"
TASK_SCRIPT="${0}"

########## Define Environments ##########
STAGING_HOSTS="10.11.12.104"
PRODUCTION_HOSTS="peterlyons.com"

########## No-Op Test Tasks for sudo, root, and normal user ##########
test:uptime() {
    uptime   
}

test:uptime_sudo() { #TASK: sudo
    uptime
}
########## OS Section ##########
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
#Needed to build node.js
make
#Needed for get_prereqs (will normally always be available on Ubuntu anyway)
python
#This is our web server
nginx
EOF
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
    if [ ! -d ~/.ssh ]; then
        mkdir ~/.ssh
    fi
    #This is plyons's public SSH key
    if ! grep "^ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEArdBAo5y" ~/.ssh/authorized_keys > /dev/null 2>&1; then
        cat <<EOF | tr -d '\n' >> ~/.ssh/authorized_keys
ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEArdBAo5yfb43w5/N3nxQvpDH6tCIvwvsJu/FrvRFiM8
+s/lGP0XxihzHYOJH/IdEz+WnjnKMBCWT/we3ZbWMFQ32yMzXAj2B+noaranIOLJ7C52uZrWoS2OOO
qWtwuj4jZLZ9v7cLvxC9v69b8dqyBOJG3YlIzqFQeYT7p4I1XWDRfwhsuX738zhvBYSx4w3tkZDmEp
sSl0+xNVjugBjNP81ynP3nUkeH+Ap2IUrJK5RnGpLXg+EX1DpPypXvn67SpHvz0+DgQuKwL+AYQdFS
86p21tuSDJ0yKz8CX+5nrJjjt2NUYgs0SwGU387UzqGFH5711C2rc9gkD6cvGbX0mQ
== zoot MacBook pro
EOF
    #Need a trailing newline
    echo >> ~/.ssh/authorized_keys
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

list_templates() {
    ls ${WORK}/app/templates/*.jade | xargs -n 1 basename | sed -e s/\.jade// \
        | sed -e /layout/d | sed -e /photos/d
}

app:start() {
    kill_stale
    NODE_ENV=test coffee server.coffee &
    echo "$!" > "${PID_FILE}"
    echo "new node process started with pid $(cat ${PID_FILE})"
    if [ $(uname) == "Darwin" ]; then
        sleep 1
        open -a "Google Chrome" "http://localhost:$(coffee bin/get_port.coffee)"
    fi   
}

app:stop() {
    kill_stale
}

app:build_static() {
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
}

app:prereqs() {
    #TODO node.js install
    #TODO npm install
    for DEP in $(python "${DIR}/bin/get_prereqs.py")
    do
        npm install "${DEP}" || exit 5
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

if [ -z "${ENV_NAME}" ]; then
    #local mode
    eval "${OP}" "${@}"
else
    #remote mode
    for HOST in ${HOSTS}
    do
        scp "${TASK_SCRIPT}" "${HOST}:/tmp"
        ssh "${HOST}" bash "/tmp/$(basename ${TASK_SCRIPT})" "${OP}" "${@}"
    done
fi
