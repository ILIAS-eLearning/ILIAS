#!/bin/bash

if [[ -z ${GHRUN} ]]
then
  RUNCSFIXER=$(vendor/composer/vendor/bin/php-cs-fixer fix --using-cache=no --dry-run -vvv --config=./scripts/PHP-CS-Fixer/code-format.php_cs $@)
  RESULT=$?
  if [[ ${RESULT} -ne 0 ]]
  then
    exit ${RESULT}
  fi
  exit 0
else
  # run at github actions
  source scripts/Import/Functions.sh

  CHANGED_FILES=$(get_changed_files)
  for FILE in ${CHANGED_FILES}
  do
  	if [ -f ${FILE} ]
  	then
	  	echo "Check file: ${FILE}"
	  	RUNCSFIXER=$(vendor/composer/vendor/bin/php-cs-fixer fix --using-cache=no --diff --config=./scripts/PHP-CS-Fixer/code-format.php_cs ${FILE})
	  	RESULT=$?
	  	if [[ ${RESULT} -ne 0 ]]
	  	then
	  		exit ${RESULT}
	    fi
	  fi
  done
  exit 0
fi
