#!/bin/sh
cd $(dirname "${0}")
BRANCH=$(git rev-parse --abbrev-ref HEAD)
./new_post.coffee "${@}"
cd ..
./do start &

prompt() {
  echo "Press return to preview in browser, type PUBLISH to publish"
  read PUBLISH
}

while [ "PUBLISH" -ne "${PUBLISH}" ]
do
  prompt
  open 'http://localhost:9000/'
done

kill $(lsof -n -i4TCP:9000 | grep LISTEN)
