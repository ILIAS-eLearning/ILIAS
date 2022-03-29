#!/bin/sh

echo "sorting langfile entries"
LANGFILES=$(git diff --cached --name-only --diff-filter=ACM -- '*.lang')
for FILE in $LANGFILES
do
	HEADER_LENGTH=$(awk '/<!-- language file start -->/ {print FNR}' ${FILE})
	(head -n $HEADER_LENGTH ${FILE} && tail ${FILE} -n +$((HEADER_LENGTH + 1)) | sort ) > ${FILE}.tmp
	mv ${FILE}.tmp ${FILE}
done
exit 0
