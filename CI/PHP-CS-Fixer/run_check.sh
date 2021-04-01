#!/bin/bash

source CI/Import/Functions.sh

CHANGED_FILES=$(get_changed_files)
for FILE in ${CHANGED_FILES}
do
	echo "Check file: ${FILE}"
	RUNCSFIXER=$(libs/composer/vendor/bin/php-cs-fixer fix --using-cache=no --diff --config=./CI/PHP-CS-Fixer/code-format.php_cs ${FILE})
	RESULT=$?
	if [[ ${RESULT} -ne 0 ]]
	then
		exit ${RESULT}
  fi
done
exit 0