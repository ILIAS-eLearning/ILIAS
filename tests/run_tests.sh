#!/bin/bash

#plugin path from ilias-root:
PLUGINPATH='Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing';
SCRIPT_PATH=$(dirname "$0");
cd $SCRIPT_PATH;

# note: no more parameters
phpunit --bootstrap ./$PLUGINPATH/libs/vendor/autoload.php --exclude-group needsInstalledILIAS tests;

#first param is path to ilias installation
if [ $1 ] ; then
	cd $1;
	echo;
	echo 'now running ILIAS tests in ' $1;
	phpunit --bootstrap ./$PLUGINPATH/libs/vendor/autoload.php $PLUGINPATH/tests
fi