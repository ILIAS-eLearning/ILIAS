#!/bin/bash
source CI/Import/Functions.sh
source CI/Import/Variables.sh

if [[ -x "$PHPSTAN" ]]
  then
  	$PHPSTAN analyse --configuration CI/PHPStan/phpstan.neon --level 3 .
	
	PIPE_EXIT_CODE=`echo ${PIPESTATUS[0]}`
	
	printLn "Command exited with code: $PIPE_EXIT_CODE"

else
	printLn "No phpstan executable found, please install it with the following command:"
	printLn "\tcd libs/composer && composer install"
fi