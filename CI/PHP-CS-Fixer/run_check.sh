#!/bin/bash
PHPFIX_RESULTS_PATH="/tmp/phpfix_results"

PRE="\t*** "

function printLn() {
	echo -e "$PRE $1"
}

libs/composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix --config=./CI/PHP-CS-Fixer/code-format.php_cs --dry-run --format=json > "$PHPFIX_RESULTS_PATH"

PIPE_EXIT_CODE=`echo ${PIPESTATUS[0]}`

printLn "Command exited with code: $PIPE_EXIT_CODE"

if [[ $PIPE_EXIT_CODE -gt 0 ]]
	then
		printLn "Errors were found, exiting with error code."
		exit 99
else
		printLn "No errors were found."
		exit 0
fi