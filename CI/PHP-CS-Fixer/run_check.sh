#!/bin/bash
PHPFIX_RESULTS_PATH="/tmp/phpfix_results"
PHP_CS_FIXER="libs/composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer"

PRE="\t*** "

function printLn() {
	echo -e "$PRE $1"
}

if [[ -x "$PHP_CS_FIXER" ]]
  then
  	$PHP_CS_FIXER fix --config=./CI/PHP-CS-Fixer/code-format.php_cs --dry-run --format=json > "$PHPFIX_RESULTS_PATH"
	
	PIPE_EXIT_CODE=`echo ${PIPESTATUS[0]}`
	
	printLn "Command exited with code: $PIPE_EXIT_CODE"
else
	printLn "No php-cs-fixer found, please install it with the following command:"
	printLn "\tcd libs/composer/ && composer install"
fi