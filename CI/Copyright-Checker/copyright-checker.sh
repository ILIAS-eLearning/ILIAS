#!/bin/bash

# Copyright Checker
#
# Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
#
# Author Laura Herzog <laura.herzog@concepts-and-training.de>
#
# This tool checks the changes files in the current pull request
# to determine if the copyright lines are at the correct place.
# Only works with php files.

source CI/Import/Functions.sh

STRINGTOCHECK="/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/"

STRINGTOCHECK="$(echo -e "${STRINGTOCHECK}" | tr -d '[:space:]')"
STRINGTOCHECK="$(echo -e "${STRINGTOCHECK}" | tr -d '*')"
STRINGTOCHECK="$(echo -e "${STRINGTOCHECK}" | tr -d '/')"

# get the files from this PR to the last head
if [[ -z ${GHRUN} ]]
then
  CHANGED_FILES=$(find . -path ./libs -prune -o -type f -name '*.php')
else
  CHANGED_FILES=$(get_changed_files)
fi

echo "Scanning changed files for copyright notice ..."

FILES=()
COUNTER=0
for PHPFILE in $CHANGED_FILES;
do
  FELONE="$(pwd)/$PHPFILE"

  if [ ! -f "$FELONE" ]; then
    continue
  fi

  if [[ $FELONE == "./libs" ]]; then
    continue
  fi

  # check for php extension
  if [ ! ${FELONE: -4} == ".php" ]; then
    continue
  fi

  FIRSTLINE=$(sed -n 1p ${FELONE})
  SECONDLINE=$(sed -n 2p ${FELONE})
  THIRDLINE=$(sed -n 3p ${FELONE})

  if [[ ${SECONDLINE} == *"/**"* ]]
  then

    COUNTER=0
    COPYLINE=""
    while IFS= read -r LINE
    do
      let COUNTER=COUNTER+1
      if (( ${COUNTER} >= 2 )) && (( ${COUNTER} <= 14 ))
      then
        COPYLINE+="${LINE}"
      fi
    done < "${FELONE}"

    COPYLINE="$(echo -e "${COPYLINE}" | tr -d '[:space:]')"
    COPYLINE="$(echo -e "${COPYLINE}" | tr -d '*')"
    COPYLINE="$(echo -e "${COPYLINE}" | tr -d '/')"

    if [[ "${COPYLINE}" == "${STRINGTOCHECK}" ]]
    then
      if [[ -z ${GHRUN} ]]
      then
        echo -ne "."
      fi
      continue
    else
      FILES+=("Copyright not as expected in $PHPFILE")
      if [[ -z ${GHRUN} ]]
      then
        echo -ne "F"
      fi
    fi
  fi

  if [[ -z ${SECONDLINE} ]] && [[ ${THIRDLINE} == *"/**"* ]]
  then

    COUNTER=0
    COPYLINE=""
    while IFS= read -r LINE
    do
      let COUNTER=COUNTER+1
      if (( ${COUNTER} >= 3 )) && (( ${COUNTER} <= 15 ))
      then
        COPYLINE+="${LINE}"
      fi
    done < "${FELONE}"

    COPYLINE="$(echo -e "${COPYLINE}" | tr -d '[:space:]')"
    COPYLINE="$(echo -e "${COPYLINE}" | tr -d '*')"
    COPYLINE="$(echo -e "${COPYLINE}" | tr -d '/')"

    if [[ "${COPYLINE}" == "${STRINGTOCHECK}" ]]
    then
      if [[ -z ${GHRUN} ]]
      then
        echo -ne "."
      fi
      continue
    else
      FILES+=("Copyright not as expected in $PHPFILE")
      if [[ -z ${GHRUN} ]]
      then
        echo -ne "F"
      fi
    fi
  else
    FILES+=("Copyright not as expected in $PHPFILE")
    if [[ -z ${GHRUN} ]]
    then
      echo -ne "F"
    fi
  fi
done

cd $DIR
AMOUNTFILES=${#FILES[@]}
echo "Scan complete. Found $AMOUNTFILES incidents."
if [ "$AMOUNTFILES" -gt "0" ]; then
  for (( i=0; i<${AMOUNTFILES}; i++ ));
  do
    LINE=${FILES[$i]}
    echo ${LINE}
  done
  exit 1
fi
exit 0
