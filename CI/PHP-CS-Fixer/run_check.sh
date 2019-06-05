#!/bin/bash
PHPFIX_RESULTS_PATH="/tmp/phpfix_results"
PHP_CS_FIXER="libs/composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer"

PRE="\t*** "

function printLn() {
	echo -e "$PRE $1"
}

if [[ -e $PHP-CS-FIXER ]]
  then
  	$PHP-CS-FIXER fix --config=./CI/PHP-CS-Fixer/code-format.php_cs --dry-run --format=json > "$PHPFIX_RESULTS_PATH"
	
	PIPE_EXIT_CODE=`echo ${PIPESTATUS[0]}`
	
	printLn "Command exited with code: $PIPE_EXIT_CODE"
else
	printLn "No php-cs-fixer found, please install it with the following command:"
	printLn "\tcomposer require friendsofphp/php-cs-fixer"
fi