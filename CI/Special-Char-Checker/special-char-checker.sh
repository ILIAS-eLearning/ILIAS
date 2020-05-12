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

# help
if [ "$1" == "help" ]; then
    echo "General usage:"
    echo "type ./charchecker.sh path_to_src_folder extension"
    echo "default extension is 'php' if no extension is given"
    exit 1;
fi

# config
if [ "$1" ]; then
    PATH_TO_SRC=$1
else
    echo "No path to folder specified. Exiting."
    exit 1;
fi

if [ "$2" ]; then
    EXTENSION=$2
else
    EXTENSION="php"
fi

# Print out bla first
echo "Scanning $PATH_TO_SRC for $EXTENSION Files ..."

# find all the php files and check them for not wanted control characters
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
FILES=()
cd $PATH_TO_SRC

AMOUNT_OF_FILES=$(find . -path ./libs -prune -o -type f -name '*.\'"$EXTENSION" | wc -l)
echo "Found $AMOUNT_OF_FILES files."

COUNTER=0
for PHPFILE in $(find . -path ./libs -prune -o -type f -name '*.'"$EXTENSION");
do
    if [[ $PHPFILE == "./libs" ]]; then
      continue
    fi

    COUNTER=$((COUNTER + 1))
    echo -ne "Scanning $COUNTER of $AMOUNT_OF_FILES"'\r';

    TWOB=$(grep -n -C 0 "$(printf %b '\u200b')" $PHPFILE) | cut -d: -f1;
    if [ "$TWOB" ]; then
        FILES+=("u200b found in $PHPFILE see line(s) $TWOB")
    fi

    TWOC=$(grep -n -C 0 "$(printf %b '\u200c')" $PHPFILE) | cut -d: -f1;
    if [ "$TWOC" ]; then
        FILES+=("u200c found in $PHPFILE see line(s) $TWOC")
    fi

    TWOD=$(grep -n -C 0 "$(printf %b '\u200d')" $PHPFILE) | cut -d: -f1;
    if [ "$TWOD" ]; then
        FILES+=("u200d found in $PHPFILE see line(s) $TWOD")
    fi

    TWOE=$(grep -n -C 0 "$(printf %b '\u200e')" $PHPFILE) | cut -d: -f1;
    if [ "$TWOE" ]; then
        FILES+=("u200e found in $PHPFILE see line(s) $TWOE")
    fi

    TWOF=$(grep -n -C 0 "$(printf %b '\u200f')" $PHPFILE | cut -d: -f1);
    if [ "$TWOF" ]; then
        FILES+=("u200f found in $PHPFILE see line(s) $TWOF")
    fi

    FEFF=$(grep -n -C 0 "$(printf %b '\ufeff')" $PHPFILE | cut -d: -f1);
    if [ "$FEFF" ]; then
        FILES+=("ufeff found in $PHPFILE see line(s) $FEFF")
    fi

    OOOT=$(grep -n -C 0 "$(printf %b '\u0003')" $PHPFILE | cut -d: -f1);
    if [ "$OOOT" ]; then
        FILES+=("u0003 found in $PHPFILE see line(s) $OOOT")
    fi

    OTWOE=$(grep -n -C 0 "$(printf %b '\u2028')" $PHPFILE | cut -d: -f1);
    if [ "$OTWOE" ]; then
        FILES+=("u2028 found in $PHPFILE see line(s) $OTWOE")
    fi

    OOAO=$(grep -n -C 0 "$(printf %b '\u00A0' | tr -d '\n')" $PHPFILE | cut -d: -f1);
    if [ "$OOAO" ]; then
        FILES+=("u00A0 found in $PHPFILE see line(s) $OOAO")
    fi
done; echo

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
