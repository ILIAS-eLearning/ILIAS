#!/bin/bash

function printLn() {
	echo -e "$PRE $1"
}

function get_changed_files() {

  if [[ -z ${PR_NUMBER} ]]
  then
    CHANGED_FILES=$(git diff-tree --name-only --diff-filter=ACMRT --no-commit-id -r ${GH_SHA} | grep '.php')
  else
    URL="https://api.github.com/repos/${GITHUB_REPOSITORY}/pulls/${PR_NUMBER}/files"
    CHANGED_FILES=$(curl -s -X GET -G $URL | jq -r '.[] | .filename' | grep '.php')
  fi
  echo ${CHANGED_FILES}
}