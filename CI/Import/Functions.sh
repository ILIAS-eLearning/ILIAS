#!/bin/bash

function printLn() {
	echo -e "$PRE $1"
}

function get_changed_files() {
  URL="https://api.github.com/repos/${GITHUB_REPOSITORY}/pulls/${PR_NUMBER}/files"
  CHANGED_FILES=$(curl -s -X GET -G $URL | jq -r '.[] | .filename' | grep '.php')
  echo ${CHANGED_FILES}
}