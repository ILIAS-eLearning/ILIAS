#!/bin/bash
source CI/Import/Functions.sh
source CI/Import/Variables.sh

if [[ -x "$PHP_CS_FIXER" ]]
  then
  	$PHP_CS_FIXER fix --config=./CI/PHP-CS-Fixer/code-format.php_cs --dry-run -vvv > "$PHP_CS_FIXER_RESULTS_PATH"
	
	PIPE_EXIT_CODE=`echo ${PIPESTATUS[0]}`
	
	printLn "Command exited with code: $PIPE_EXIT_CODE"

	printLn "Cloning results repository, copy results file."
	if [ -d "$TRAVIS_RESULTS_DIRECTORY" ]; then
		printLn "Starting to remove old temp directory"
		rm -rf "$TRAVIS_RESULTS_DIRECTORY"
	fi

	cd /tmp && git clone https://github.com/ILIAS-eLearning/CI-Results

	printLn "Switching directory and run results handling."
	cp "$PHP_CS_FIXER_RESULTS_PATH" "$TRAVIS_RESULTS_DIRECTORY/data/"
	cd "$TRAVIS_RESULTS_DIRECTORY" && ./run.sh
	
else
	printLn "No php-cs-fixer found, please install it with the following command:"
	printLn "\tcomposer require friendsofphp/php-cs-fixer"
fi