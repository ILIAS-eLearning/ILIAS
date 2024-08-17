#!/bin/bash

if [[ -z ${GHRUN} ]]; then
  RUNCSFIXER=$(vendor/composer/vendor/bin/php-cs-fixer fix --using-cache=no --dry-run -vvv --config=./scripts/PHP-CS-Fixer/code-format.php_cs $@)
  RESULT=$?
  if [[ ${RESULT} -ne 0 ]]; then
    exit ${RESULT}
  fi
else
  # run at github actions
  source scripts/Import/Functions.sh

  CHANGED_FILES=$(get_changed_files)
  if [[ -z "${CHANGED_FILES[@]}" ]]; then
      exit 0
  fi

  vendor/composer/vendor/bin/php-cs-fixer fix --using-cache=no --dry-run --config=./scripts/PHP-CS-Fixer/code-format.php_cs "${CHANGED_FILES[@]}"
  RESULT=$?
  if [[ ${RESULT} -ne 0 ]]; then
    exit ${RESULT}
  fi
fi

exit 0
