#!/bin/bash

source CI/Import/Functions.sh

CHECKS=true
CHECKFLAG=${1}

header_end='<!-- language file start -->'

langfiles=$(find . -name "*.lang")

for file in $langfiles
do
    header_length=1
    while [ true ]
    do
        header_length=$(($header_length + 1))
        line=$(head -n ${header_length} $file | tail -n1)

        if [ "$line" = "$header_end" ]; then
            break
        fi

        if [ $header_length -gt 64 ]; then
            echo "no proper header-marker found in ${file}; maybe check on line endings?"
            break
        fi
    done

    (cat ${file} | php -r '$c=explode("\n",file_get_contents("php://stdin"));array_multisort($c);file_put_contents("php://stdout",join("\n",$c));') > ${file}.tmp

    LANGFILEDIFF=$(diff ${file} ${file}.tmp)
    if [ ! -z "${LANGFILEDIFF}" ]
    then
        if [ ! -z ${CHECKFLAG} ]
        then
            CHECKS=false
            echo "language file ${file} needs to be sorted."
        else
            cp ${file}.tmp ${file}
            echo "modified ${file}"
        fi
    fi
    rm ${file}.tmp
done

if [ $CHECKS = false ]
then
    exit 127
else
    exit 0
fi
