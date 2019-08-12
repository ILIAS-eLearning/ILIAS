#!/bin/bash
PHPFIX_RESULTS_PATH="/tmp/phpfix_results"
PHP_CS_FIXER="libs/composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer"
TRAVIS_RESULTS_DIRECTORY="/tmp/CI-Results/"

PRE="\t*** "

function printLn() {
	echo -e "$PRE $1"
}

if [[ -x "$PHP_CS_FIXER" ]]
  then
  	$PHP_CS_FIXER fix --config=./CI/PHP-CS-Fixer/code-format.php_cs --dry-run -vvv > "$PHPFIX_RESULTS_PATH"
	
	PIPE_EXIT_CODE=`echo ${PIPESTATUS[0]}`
	
	printLn "Command exited with code: $PIPE_EXIT_CODE"

	printLn "Cloning results repository, copy results file."
	if [ -d "$TRAVIS_RESULTS_DIRECTORY" ]; then
		printLn "Starting to remove old temp directory"
		rm -rf "$TRAVIS_RESULTS_DIRECTORY"
	fi

	cd /tmp && git clone https://github.com/ILIAS-eLearning/CI-Results

	printLn "Switching directory and run results handling."
	cp "$PHPFIX_RESULTS_PATH" "$TRAVIS_RESULTS_DIRECTORY/data/"
	cd "$TRAVIS_RESULTS_DIRECTORY" && ./run.sh
	
else
	printLn "No php-cs-fixer found, please install it with the following command:"
	printLn "\tcd libs/composer/ && composer install"
fi