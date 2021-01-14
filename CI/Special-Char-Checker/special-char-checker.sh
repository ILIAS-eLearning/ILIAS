#!/bin/bash

# Special Char Checker
#
# Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
#
# Author Laura Herzog <laura.herzog@concepts-and-training.de>
#
# This tool checks the file within the given path for special control chars
# which are usually used by old printers. Sometimes these hidden characters
# like hidden spaces, tabs or newlines are added to the code accidentally.


# get the files from this PR to the last head
GITFILES=$(git --no-pager diff --name-only FETCH_HEAD $(git merge-base FETCH_HEAD ${TRAVIS_BRANCH}))

echo "Scanning changed files for special chars ..."

FILES=()
COUNTER=0
for PHPFILE in $GITFILES;
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

  TWOB=$(grep -n -C 0 "$(printf %b '\u200b')" $FELONE) | cut -d: -f1;
  if [ "$TWOB" ]; then
    FILES+=("u200b found in $PHPFILE see line(s) $TWOB")
  fi

  TWOC=$(grep -n -C 0 "$(printf %b '\u200c')" $FELONE) | cut -d: -f1;
  if [ "$TWOC" ]; then
    FILES+=("u200c found in $PHPFILE see line(s) $TWOC")
  fi

  TWOD=$(grep -n -C 0 "$(printf %b '\u200d')" $FELONE) | cut -d: -f1;
  if [ "$TWOD" ]; then
    FILES+=("u200d found in $PHPFILE see line(s) $TWOD")
  fi

  TWOE=$(grep -n -C 0 "$(printf %b '\u200e')" $FELONE) | cut -d: -f1;
  if [ "$TWOE" ]; then
    FILES+=("u200e found in $PHPFILE see line(s) $TWOE")
  fi

  TWOF=$(grep -n -C 0 "$(printf %b '\u200f')" $FELONE | cut -d: -f1);
  if [ "$TWOF" ]; then
    FILES+=("u200f found in $PHPFILE see line(s) $TWOF")
  fi

  FEFF=$(grep -n -C 0 "$(printf %b '\ufeff')" $FELONE | cut -d: -f1);
  if [ "$FEFF" ]; then
    FILES+=("ufeff found in $PHPFILE see line(s) $FEFF")
  fi

  OOOT=$(grep -n -C 0 "$(printf %b '\u0003')" $FELONE | cut -d: -f1);
  if [ "$OOOT" ]; then
    FILES+=("u0003 found in $PHPFILE see line(s) $OOOT")
  fi

  OTWOE=$(grep -n -C 0 "$(printf %b '\u2028')" $FELONE | cut -d: -f1);
  if [ "$OTWOE" ]; then
    FILES+=("u2028 found in $PHPFILE see line(s) $OTWOE")
  fi

  OOAO=$(grep -n -C 0 "$(printf %b '\u00A0' | tr -d '\n')" $FELONE | cut -d: -f1);
  if [ "$OOAO" ]; then
    FILES+=("u00A0 found in $PHPFILE see line(s) $OOAO")
  fi
done;

cd $DIR
AMOUNTFILES=${#FILES[@]}
SEARCH="--"
REPLACE=", "
echo "Scan complete. Found $AMOUNTFILES incidents."
if [ "$AMOUNTFILES" -gt "0" ]; then
  for (( i=0; i<${AMOUNTFILES}; i++ ));
  do
    LINE=${FILES[$i]}
    echo ${LINE//$SEARCH/$REPLACE}
  done
  exit 1
fi
exit 0