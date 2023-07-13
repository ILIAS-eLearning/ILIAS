#!/bin/bash

# This file is part of ILIAS, a powerful learning management system
# published by ILIAS open source e-Learning e.V.
#
# ILIAS is licensed with the GPL-3.0,
# see https://www.gnu.org/licenses/gpl-3.0.en.html
# You should have received a copy of said license along with the
# source code, too.
#
# If this is not the case or you just want to try ILIAS, you'll find
# us at:
# https://www.ilias.de
# https://github.com/ILIAS-eLearning
#
# This script compares the actual style repo with the built style folder and pushes the possible changes to repo.

NOW=$(date +'%d.%m.%Y %I:%M:%S')
DEPLOY_BASE_FOLDER="./CI/Style-To-Repo/repo"
STYLE_REPO="https://github.com/ILIAS-eLearning/delos.git"
STYLE_REPO_NAME_SHORT="ILIAS-eLearning/delos.git"

function deploy() {
  MSG=${1}
  HASH=${2}
  URL=${3}
  BRANCH=${4}
  REPO_TOKEN="https://${5}@github.com/${STYLE_REPO_NAME_SHORT}"
  USER_NAME=${6}

  if [ -d ${DEPLOY_BASE_FOLDER} ]
  then
    rm -rf ${DEPLOY_BASE_FOLDER}
  fi

  mkdir -p ${DEPLOY_BASE_FOLDER}
  git clone ${REPO_TOKEN} ${DEPLOY_BASE_FOLDER} >/dev/null 2>&1
  git -C ${DEPLOY_BASE_FOLDER} ls-remote --exit-code --heads origin ${BRANCH} >/dev/null 2>&1
  BRANCH_EXISTS=$?

  if [ ${BRANCH_EXISTS} == "0" ]
  then
    git -C ${DEPLOY_BASE_FOLDER} checkout ${BRANCH} >/dev/null 2>&1
  else
    git -C ${DEPLOY_BASE_FOLDER} checkout -b ${BRANCH} >/dev/null 2>&1
    NEW_BRANCH="1"
  fi

  rm -rf ${DEPLOY_BASE_FOLDER}/*

  cp -r CI/Style-To-Repo/style/* ${DEPLOY_BASE_FOLDER}

  git -C ${DEPLOY_BASE_FOLDER} remote set-url origin ${REPO_TOKEN}
  git -C ${DEPLOY_BASE_FOLDER} config user.name ${USER_NAME}

  if [ "${NEW_BRANCH}" == "1" ]
  then
    echo "[${NOW}] Detected new branch '${BRANCH}', which will be committed to ${STYLE_REPO}"
    git -C ${DEPLOY_BASE_FOLDER} add . >/dev/null 2>&1
    git -C ${DEPLOY_BASE_FOLDER} commit -m "Style changes from '${HASH}'" -m "Original message: '${MSG}'" -m "${URL}" >/dev/null 2>&1
    git -C ${DEPLOY_BASE_FOLDER} push origin ${BRANCH} >/dev/null 2>&1
    exit
  fi

  git -C ${DEPLOY_BASE_FOLDER} update-index --really-refresh >/dev/null 2>&1
  git -C ${DEPLOY_BASE_FOLDER} diff-index --exit-code HEAD
  CHECK1=$?
  test -z "$(git -C ${DEPLOY_BASE_FOLDER} ls-files --others)"
  CHECK2=$?

  if [[ "${CHECK1}" == "0" && "${CHECK2}" == "0" ]]
  then
    echo "[${NOW}] No changes detected on style files."
  else
    echo "[${NOW}] Detected changes on style files, which will be committed to ${STYLE_REPO}"
    git -C ${DEPLOY_BASE_FOLDER} status
    git -C ${DEPLOY_BASE_FOLDER} ls-files --others
    git -C ${DEPLOY_BASE_FOLDER} add .
    git -C ${DEPLOY_BASE_FOLDER} ls-files --others
    git -C ${DEPLOY_BASE_FOLDER} status
    git -C ${DEPLOY_BASE_FOLDER} commit -m "Style changes from '${HASH}'" -m "Original message: '${MSG}'" -m "${URL}"
    git -C ${DEPLOY_BASE_FOLDER} push origin ${BRANCH}
  fi
}
