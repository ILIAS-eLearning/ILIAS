#!/bin/bash

function printLn() {
	echo -e "$PRE $1"
}

function get_changed_files() {
  URL="https://api.github.com/repos/${GITHUB_REPOSITORY}/pulls/${PR_NUMBER}/files"
  CHANGED_FILES=$(curl -s -X GET -G $URL | jq -r '.[] | .filename' | grep '.php')
  echo ${CHANGED_FILES}
}

function get_changed_lang_files() {

  if [[ -z ${PR_NUMBER} ]]
  then
    CHANGED_FILES=$(git diff-tree --name-only --diff-filter=ACMRT --no-commit-id -r ${GH_SHA} | grep '.lang')
  else
    URL="https://api.github.com/repos/${GITHUB_REPOSITORY}/pulls/${PR_NUMBER}/files"
    CHANGED_FILES=$(curl -s -X GET -G $URL | jq -r '.[] | .filename' | grep '.lang')
  fi
  echo ${CHANGED_FILES}
}