#!/bin/bash
PHPSTAN="libs/composer/vendor/bin/phpstan"

PRE="\t*** "

function printLn() {
	echo -e "$PRE $1"
}

if [[ -x "$PHPSTAN" ]]
  then
  	$PHPSTAN analyse --configuration CI/PHPStan/phpstan.neon --level 3 .
	
	PIPE_EXIT_CODE=`echo ${PIPESTATUS[0]}`
	
	printLn "Command exited with code: $PIPE_EXIT_CODE"

else
	printLn "No phpstan executable found, please install it with the following command:"
	printLn "\tcd libs/composer && composer install"
fi