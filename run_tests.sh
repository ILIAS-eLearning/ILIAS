#!/bin/bash

PRE="\t*** "

function printLn() {
	echo -e "$PRE $1"
}

printLn "Initialize paths variables"

PHPUNIT_PATH="/tmp/phpunit_latest.csv"
PHPUNIT_PATH_TMP="/tmp/phpunit_changed.csv"
PHPUNIT_RESULTS_PATH="/tmp/phpunit_results"
DICTO_PATH="/tmp/dicto_latest.csv"
TRAVIS_RESULTS_DIRECTORY="/tmp/CI-Results/"
DATE=`date '+%Y-%m-%d-%H:%M:%S'`

libs/composer/vendor/phpunit/phpunit/phpunit --bootstrap ./libs/composer/vendor/autoload.php --configuration ./Services/PHPUnit/config/PhpUnitConfig.xml --exclude-group needsInstalledILIAS --verbose $@ | tee "$PHPUNIT_RESULTS_PATH"

printLn "Travis: event type ($TRAVIS_EVENT_TYPE), job number ($TRAVIS_JOB_NUMBER), pull request ($TRAVIS_PULL_REQUEST), commit ($TRAVIS_COMMIT) "

if [[ -e "$PHPUNIT_RESULTS_PATH" && "$TRAVIS_EVENT_TYPE" != "pull_request" ]]
	then
		printLn "Collecting data."
		RESULT=`tail -n1 < "$PHPUNIT_RESULTS_PATH"`
		SPLIT_RESULT=(`echo $RESULT | tr ':' ' '`)
		PHP_VERSION=`php -r "echo PHP_MAJOR_VERSION . '_' . PHP_MINOR_VERSION;"`
		if [ -e "include/inc.ilias_version.php" ]
			then
				ILIAS_VERSION=`php -r "require_once 'include/inc.ilias_version.php'; echo ILIAS_VERSION_NUMERIC;"`
				ILIAS_VERSION=`echo "$ILIAS_VERSION" | tr . _`
		fi

		JOB_ID=`echo $TRAVIS_JOB_NUMBER`
		JOB_URL=`echo $TRAVIS_JOB_WEB_URL`
		FAILURE=false
		declare -A RESULTS=([Tests]=0 [Assertions]=0 [Errors]=0 [Warnings]=0 [Skipped]=0 [Incomplete]=0 [Risky]=0);

		for TYPE in "${!RESULTS[@]}"; 
			do 
				for PHP_UNIT_RESULT in "${!SPLIT_RESULT[@]}"; 
					do 
						if [ "$TYPE" == "${SPLIT_RESULT[$PHP_UNIT_RESULT]}" ]
							then
								CLEANED=(`echo ${SPLIT_RESULT[$PHP_UNIT_RESULT + 1]} | tr ',.' ' '`)
								RESULTS[$TYPE]=$CLEANED;
						fi
					done 
			done

		if [ ${RESULTS[Errors]} -gt 0 ]
			then
				FAILURE=true
		fi

		printLn "Cloning results repository, copy results file."
		if [ -d "$TRAVIS_RESULTS_DIRECTORY" ]; then
			printLn "Starting to remove old temp directory"
			rm -rf "$TRAVIS_RESULTS_DIRECTORY"
		fi

		cd /tmp && git clone https://github.com/ILIAS-eLearning/CI-Results
		cp "$TRAVIS_RESULTS_DIRECTORY/data/phpunit_latest.csv" "$PHPUNIT_PATH"

		printLn "Removing old line PHP version $PHP_VERSION and ILIAS version $ILIAS_VERSION"
		grep -v "$ILIAS_VERSION.*php_$PHP_VERSION" $PHPUNIT_PATH > $PHPUNIT_PATH_TMP 

		NEW_LINE="$JOB_URL,$JOB_ID,$ILIAS_VERSION,php_$PHP_VERSION,PHP $PHP_VERSION,${RESULTS[Warnings]},${RESULTS[Skipped]},${RESULTS[Incomplete]},${RESULTS[Tests]},${RESULTS[Errors]},${RESULTS[Risky]},$FAILURE,$DATE";
		printLn "Writing line: $NEW_LINE"
		echo "$NEW_LINE" >> "$PHPUNIT_PATH_TMP";

		printLn "Handling result."

		if [ -e "$PHPUNIT_PATH_TMP" ]
			then
				mv "$PHPUNIT_PATH_TMP" "$PHPUNIT_PATH"
				rm "$PHPUNIT_RESULTS_PATH"
		fi

		#printLn "Switching directory and run results handling."
		#cp "$PHPUNIT_PATH" "$TRAVIS_RESULTS_DIRECTORY/data/"
		#cd "$TRAVIS_RESULTS_DIRECTORY" && ./run.sh
else
	printLn "No result file found, stopping!"
	exit 99
fi