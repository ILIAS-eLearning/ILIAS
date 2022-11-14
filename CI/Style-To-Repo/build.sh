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
# This script gather all style depending files and add them to a folder.

BUILD_BASE_FOLDER="./CI/Style-To-Repo/style"

function build() {
  if [ -d ${BUILD_BASE_FOLDER} ]
  then
    rm -rf ${BUILD_BASE_FOLDER}
  fi

  mkdir -p ${BUILD_BASE_FOLDER}

  mkdir ${BUILD_BASE_FOLDER}/UI
  mkdir ${BUILD_BASE_FOLDER}/Services
  mkdir ${BUILD_BASE_FOLDER}/Modules

  cp -r ./templates/default/* ${BUILD_BASE_FOLDER}
  cp -r ./src/UI/templates/default/* ${BUILD_BASE_FOLDER}/UI


  declare -a SERVICES

  SERVICES=($(find **/*/templates/default -type d | grep ^Services))

  for SERVICE in "${SERVICES[@]}"
  do
    NAME=$(echo ${SERVICE} | cut -d'/' -f2- | rev | cut -d'/' -f3- | rev)
    if [[ "${NAME}" == *\/* ]]
    then
     continue
    fi
    mkdir -p ${BUILD_BASE_FOLDER}/Services/${NAME}
    cp -r ${SERVICE}/* ${BUILD_BASE_FOLDER}/Services/${NAME}
  done

  declare -a MODULES

  MODULES=($(find **/*/templates/default -type d | grep ^Modules))

  for MODULE in "${MODULES[@]}"
  do
    NAME=$(echo ${MODULE} | cut -d'/' -f2- | rev | cut -d'/' -f3- | rev)
    if [[ "${NAME}" == *\/* ]]
    then
     continue
    fi
    mkdir -p ${BUILD_BASE_FOLDER}/Modules/${NAME}
    cp -r ${MODULE}/* ${BUILD_BASE_FOLDER}/Modules/${NAME}
  done
}