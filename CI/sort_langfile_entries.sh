#!/bin/sh

header_end='<!-- language file start -->'
langfiles=$(git diff --cached --name-only --diff-filter=ACM -- '*.lang')

for file in $langfiles
do
    header_length=1
    while [ true ]
    do
        header_length=$(($header_length + 1))
        line=$(head $file -n ${header_length} | tail -n1)

        if [ "$line" = "$header_end" ]; then
            break
        fi

        if [ $header_length -gt 64 ]; then
            echo "no proper header-marker found in ${file}; maybe check on line endings?"
            break
        fi
    done

    echo  "sorting entries in ${file}";
    (head ${file} -n $header_length && tail ${file} -n +$((header_length + 1)) | sort ) > ${file}.tmp
    mv ${file}.tmp ${file}

done
exit 0
