#!/bin/bash

if [[ -z ${GHRUN} ]]
then
  RUNCSFIXER=$(libs/composer/vendor/bin/php-cs-fixer fix --using-cache=no --dry-run -vvv --config=./CI/PHP-CS-Fixer/code-format.php_cs)
  RESULT=$?
  if [[ ${RESULT} -ne 0 ]]
  then
    exit ${RESULT}
  fi
  exit 0
else
  # run at github actions
  source CI/Import/Functions.sh

  CHANGED_FILES=$(get_changed_files)
  for FILE in ${CHANGED_FILES}
  do
  	if [ -f ${FILE} ]
  	then
	  	echo "Check file: ${FILE}"
	  	RUNCSFIXER=$(libs/composer/vendor/bin/php-cs-fixer fix --using-cache=no --diff --config=./CI/PHP-CS-Fixer/code-format.php_cs ${FILE})
	  	RESULT=$?
	  	if [[ ${RESULT} -ne 0 ]]
	  	then
	  		exit ${RESULT}
	    fi
	  fi
  done
  exit 0
fi