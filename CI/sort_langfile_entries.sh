#!/bin/sh

echo "sorting langfile entries"
HEADER_LENGTH=16
LANGFILES=$(git diff --cached --name-only --diff-filter=ACM -- '*.lang')
for FILE in $LANGFILES
do
	(head -n $HEADER_LENGTH ${FILE} && tail ${FILE} -n +$((HEADER_LENGTH + 1)) | sort ) > ${FILE}.tmp
	mv ${FILE}.tmp ${FILE}
done
exit 0