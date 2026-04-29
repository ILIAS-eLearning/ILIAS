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

BUILD_BASE_FOLDER="./scripts/Style-To-Repo/style"

function build() {
  if [ -d ${BUILD_BASE_FOLDER} ]
  then
    rm -rf ${BUILD_BASE_FOLDER}
  fi

  mkdir -p ${BUILD_BASE_FOLDER}/delos
  cp -r ./templates/default/* ${BUILD_BASE_FOLDER}/delos

  mkdir -p ${BUILD_BASE_FOLDER}/fonts
  cp -r ./components/ILIAS/UI/resources/fonts/* ${BUILD_BASE_FOLDER}/fonts

  mkdir -p ${BUILD_BASE_FOLDER}/images
  cp -r ./components/ILIAS/UI/resources/images/* ${BUILD_BASE_FOLDER}/images

  declare -a DEFAULT_TEMPLATE_FOLDERS
  DEFAULT_TEMPLATE_FOLDERS=($(find ./components -type d -ipath '*templates/default*' | grep -v ^'./public/'))
  for DEFAULT_TEMPLATE_FOLDER in "${DEFAULT_TEMPLATE_FOLDERS[@]}"
  do
    NAME=$(echo "${DEFAULT_TEMPLATE_FOLDER}" | sed -e 's|/templates/default||')
    mkdir -p "${BUILD_BASE_FOLDER}"/"${NAME}"
    cp -r "${DEFAULT_TEMPLATE_FOLDER}"/*.html "${BUILD_BASE_FOLDER}"/"${NAME}" &> /dev/null
  done

  if [[ -d "./components/ILIAS/Mail/templates/default/img" && -d "${BUILD_BASE_FOLDER}/components/ILIAS/Mail" ]]; then
      cp -r "./components/ILIAS/Mail/templates/default/img" "${BUILD_BASE_FOLDER}/components/ILIAS/Mail" &> /dev/null
  fi

  mv ${BUILD_BASE_FOLDER}/delos/template.xml ${BUILD_BASE_FOLDER}/template.xml

  sed -i 's/Delos/SkinRepoDelos/' ${BUILD_BASE_FOLDER}/template.xml

  cp -r ./templates/Readme.md ${BUILD_BASE_FOLDER}/Readme.md
  cp -r ./templates/Guidelines_SCSS-Coding.md ${BUILD_BASE_FOLDER}/Guidelines_SCSS-Coding.md
}